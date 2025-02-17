ALTER PROCEDURE [dbo].[spi_cheque_a_fecha](@ve_fecha DATETIME, @ve_cod_usuario NUMERIC)
AS
BEGIN
	--SE EJECUTA RENTAL PARA ACTUALIZAR EL NEW_FECHA_DOC EN RENTAL
	EXEC RENTAL.dbo.spi_cheque_a_fecha @ve_fecha, 1

	DECLARE
	            @vl_fecha_actual	DATETIME = GETDATE()
	
	DELETE INF_CHEQUE_FECHA WHERE COD_USUARIO = @ve_cod_usuario
	
    --MH 13-01-2024 NO RECUERDO EL MOTIVO DE ESTE UPDATE PERO LO ACOTAREMOS DESDE EL COD_INGRESO_PAGO > 58543
	UPDATE DOC_INGRESO_PAGO SET NEW_FECHA_DOC = FECHA_DOC WHERE COD_INGRESO_PAGO > 58543 AND NEW_FECHA_DOC IS NULL
	
	/*********************************COMERCIAL*******************************************/

    --MH 13-01-2025 INSERTA LOS DOC_INGRESO_PAGO COD_TIPO_DOC_PAGO IN (2, 12) (CHEQUE, CHEQUE A FECHA)
	INSERT INTO INF_CHEQUE_FECHA
		        (FECHA_INF_CHEQUE_FECHA
                ,COD_USUARIO
                ,COD_NOTA_VENTA			
                ,NOM_EMPRESA
                ,RUT	
                ,FECHA_DOC			
                ,NRO_DOC				
                ,MONTO_DOC
                ,COD_BANCO
                ,ORIGEN_CHEQUE
                ,COD_DOC
                ,COD_ITEM_DOC
                ,TIPO_DOC
                ,NOM_TIPO_DOC)

	SELECT      @vl_fecha_actual
                ,@ve_cod_usuario 
                ,NULL																						
                ,E.NOM_EMPRESA																				
                ,CONVERT(VARCHAR,dbo.number_format(E.RUT, 0, ',', '.'))+'-'+CONVERT(VARCHAR, E.DIG_VERIF)
                ,DIP.NEW_FECHA_DOC																			
                ,DIP.NRO_DOC																			
                ,DIP.MONTO_DOC																				
                ,DIP.COD_BANCO																			
                ,'Comercial'
                ,IP.COD_INGRESO_PAGO
                ,DIP.COD_DOC_INGRESO_PAGO
                ,'IP'
                ,'CHEQUE'
	
    FROM        DOC_INGRESO_PAGO DIP
		        ,INGRESO_PAGO IP
		        ,EMPRESA E

	WHERE       DIP.COD_TIPO_DOC_PAGO IN (2, 12)    --(CHEQUE, CHEQUE A FECHA)
	            AND DIP.NEW_FECHA_DOC >= @ve_fecha
	            AND IP.COD_INGRESO_PAGO = DIP.COD_INGRESO_PAGO
	            AND IP.COD_ESTADO_INGRESO_PAGO = 2	--(CONFIRMADO)
	            AND E.COD_EMPRESA = IP.COD_EMPRESA

    --MH 13-01-2025 INSERTA LOS DOC_INGRESO_PAGO COD_TIPO_DOC_PAGO IN (1) (EFECTIVO)
	INSERT INTO INF_CHEQUE_FECHA
		        (FECHA_INF_CHEQUE_FECHA
                ,COD_USUARIO
                ,COD_NOTA_VENTA			
                ,NOM_EMPRESA
                ,RUT	
                ,FECHA_DOC			
                ,NRO_DOC				
                ,MONTO_DOC
                ,COD_BANCO
                ,ORIGEN_CHEQUE
                ,COD_DOC
                ,COD_ITEM_DOC
                ,TIPO_DOC
                ,NOM_TIPO_DOC)

	SELECT      @vl_fecha_actual
                ,@ve_cod_usuario 
                ,NULL																						
                ,E.NOM_EMPRESA																				
                ,CONVERT(VARCHAR,dbo.number_format(E.RUT, 0, ',', '.'))+'-'+CONVERT(VARCHAR, E.DIG_VERIF)
                ,DIP.NEW_FECHA_DOC																			
                ,1																			
                ,DIP.MONTO_DOC																				
                ,39 --(ITAU)																		
                ,'Comercial'
                ,IP.COD_INGRESO_PAGO
                ,DIP.COD_DOC_INGRESO_PAGO
                ,'IP'
                ,'EFECTIVO'
	
    FROM        DOC_INGRESO_PAGO DIP
		        ,INGRESO_PAGO IP
		        ,EMPRESA E

	WHERE       DIP.COD_TIPO_DOC_PAGO = 1    --(EFECTIVO)
	            -- AND DIP.NEW_FECHA_DOC >= @ve_fecha
                AND DIP.COD_DOC_INGRESO_PAGO NOT IN (SELECT ICFE.COD_DOC_INGRESO_PAGO FROM INF_CHEQUE_FECHA_EFECTIVO ICFE)
                AND DIP.COD_INGRESO_PAGO > 71885
	            AND IP.COD_INGRESO_PAGO = DIP.COD_INGRESO_PAGO
	            AND IP.COD_ESTADO_INGRESO_PAGO = 2	--(CONFIRMADO)
	            AND E.COD_EMPRESA = IP.COD_EMPRESA

	DECLARE C_TEMP INSENSITIVE  CURSOR FOR
                SELECT COD_DOC
                FROM INF_CHEQUE_FECHA
                WHERE COD_USUARIO = @ve_cod_usuario
                AND ORIGEN_CHEQUE = 'Comercial'
	
                DECLARE
                    @vc_cod_doc			NUMERIC
                    ,@vc_cod_nota_venta	NUMERIC
                    ,@vl_NVs			VARCHAR(100)

                OPEN C_TEMP
                    FETCH C_TEMP INTO @vc_cod_doc
                    WHILE @@FETCH_STATUS = 0 BEGIN

                        SET @vl_NVs = ''
                        --PAGOS A FA
                        DECLARE C_NV_FA INSENSITIVE CURSOR FOR
                            SELECT F.COD_DOC	
                            FROM INGRESO_PAGO_FACTURA IPF, FACTURA F
                            WHERE IPF.COD_INGRESO_PAGO = @vc_cod_doc
                            AND IPF.TIPO_DOC = 'FACTURA'
                            AND IPF.MONTO_ASIGNADO <> 0
                            AND F.COD_FACTURA = IPF.COD_DOC
                            AND F.COD_TIPO_FACTURA = 1	--(VENTA DESDE NV)
                        
                            OPEN C_NV_FA
                                FETCH C_NV_FA INTO @vc_cod_nota_venta
                                WHILE @@FETCH_STATUS = 0 BEGIN
                                    SET @vl_NVs = @vl_NVs + CONVERT(VARCHAR, @vc_cod_nota_venta) + '-'
                                        
                                    FETCH C_NV_FA INTO @vc_cod_nota_venta
                                END
                            CLOSE C_NV_FA
                        DEALLOCATE C_NV_FA
                    
                        --PAGOS A NV
                        DECLARE C_NV INSENSITIVE CURSOR FOR
                            SELECT N.COD_NOTA_VENTA
                            FROM INGRESO_PAGO_FACTURA IPF, NOTA_VENTA N
                            WHERE IPF.COD_INGRESO_PAGO = @vc_cod_doc
                            AND IPF.TIPO_DOC = 'NOTA_VENTA'
                            AND IPF.MONTO_ASIGNADO <> 0
                            AND N.COD_NOTA_VENTA = IPF.COD_DOC
                        
                            OPEN C_NV
                                FETCH C_NV INTO @vc_cod_nota_venta
                                WHILE @@FETCH_STATUS = 0 BEGIN
                                    SET @vl_NVs = @vl_NVs + CONVERT(VARCHAR, @vc_cod_nota_venta) + '-'
                                    FETCH C_NV INTO @vc_cod_nota_venta
                                END
                            CLOSE C_NV
                        DEALLOCATE C_NV
                        
                        IF(@vl_NVs <> '')
                            SET @vl_NVs = LEFT(@vl_NVs, len(@vl_NVs) - 1)	--(BORRA EL ULTIMO "-")
                            
                        UPDATE INF_CHEQUE_FECHA SET COD_NOTA_VENTA = @vl_NVs WHERE cod_doc = @vc_cod_doc AND ORIGEN_CHEQUE = 'Comercial'
                        FETCH C_TEMP INTO @vc_cod_doc
                    END
                CLOSE C_TEMP
	DEALLOCATE C_TEMP
	/*********************************COMERCIAL*******************************************/

	/***********************************RENTAL********************************************/
	INSERT INTO INF_CHEQUE_FECHA
                (FECHA_INF_CHEQUE_FECHA
                ,COD_USUARIO
                ,COD_NOTA_VENTA			
                ,NOM_EMPRESA
                ,RUT	
                ,FECHA_DOC			
                ,NRO_DOC				
                ,MONTO_DOC
                ,COD_BANCO
                ,ORIGEN_CHEQUE
                ,COD_DOC
                ,COD_ITEM_DOC
                ,TIPO_DOC
                ,NOM_TIPO_DOC)

	SELECT      @vl_fecha_actual
                ,@ve_cod_usuario 
                ,NULL													
                ,E.NOM_EMPRESA												
                ,CONVERT(VARCHAR,dbo.number_format(e.RUT, 0, ',', '.'))+'-'+CONVERT(VARCHAR, E.DIG_VERIF)
                ,DIP.NEW_FECHA_DOC
                ,DIP.NRO_DOC
                ,DIP.MONTO_DOC
                ,DIP.COD_BANCO
                ,'Rental'
                ,IP.COD_INGRESO_PAGO
                ,DIP.COD_DOC_INGRESO_PAGO
                ,'IP'
                ,'CHEQUE'

	FROM        RENTAL.dbo.DOC_INGRESO_PAGO DIP
                ,RENTAL.dbo.INGRESO_PAGO IP
                ,RENTAL.dbo.EMPRESA E

	WHERE       DIP.COD_TIPO_DOC_PAGO IN (1,2,12)	--cheque, cheque a fecha
                AND DIP.NEW_FECHA_DOC >= @ve_fecha
                AND DIP.COD_CHEQUE IS NULL
                AND IP.COD_INGRESO_PAGO = DIP.COD_INGRESO_PAGO
                AND IP.COD_ESTADO_INGRESO_PAGO = 2	--confirmado
                AND E.COD_EMPRESA = IP.COD_EMPRESA
	UNION
	SELECT      @vl_fecha_actual
                ,@ve_cod_usuario
                ,NULL
                ,E.NOM_EMPRESA
                ,CONVERT(VARCHAR ,dbo.number_format(E.RUT, 0, ',', '.'))+'-'+CONVERT(VARCHAR, E.DIG_VERIF)
                ,FECHA_DOC
                ,NRO_DOC
                ,MONTO_DOC
                ,COD_BANCO
                ,'Rental'
                ,ic.COD_INGRESO_CHEQUE
                ,c.COD_CHEQUE
                ,'RCC'
                ,'CHEQUE'

	FROM        RENTAL.dbo.CHEQUE C
		        ,RENTAL.dbo.INGRESO_CHEQUE IC
		        ,RENTAL.dbo.EMPRESA E
	WHERE       FECHA_DOC >= @ve_fecha

	/*
	15/05/2023 MH: No se usa esta funcion ya que solo tiene que desplegar informacion de los cheques en el informe
	AND RENTAL.dbo.f_ch_saldo(COD_CHEQUE) > 0
	*/

	AND IC.COD_ESTADO_INGRESO_CHEQUE = 2
	AND COD_TIPO_DOC_PAGO in (1,2,12)
	AND ES_GARANTIA = 'N'
	AND IC.COD_INGRESO_CHEQUE = C.COD_INGRESO_CHEQUE
	AND IC.COD_EMPRESA = E.COD_EMPRESA
	ORDER BY NEW_FECHA_DOC ASC
	
	/***********************************RENTAL********************************************/


	/******** INI AJUSTE PARA DEJAR FUIERA EL INGRESO DE PAGO NRO 70892 ********/
    DELETE INF_CHEQUE_FECHA WHERE COD_DOC = 70892 AND COD_USUARIO IN (1,2)
	/******** FIN AJUSTE PARA DEJAR FUIERA EL INGRESO DE PAGO NRO 70892 ********/

END