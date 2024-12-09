<?php
require_once(dirname(__FILE__)."/../../../../commonlib/trunk/php/auto_load.php");

class dw_item_informe_comision_fa extends datawindow{
	function dw_item_informe_comision_fa(){
		$sql = "SELECT COD_ITEM_INFORME_COMISION
                    ,COD_INFORME_COMISION
                    ,TIPO_DOCUMENTO
                    ,COD_DOC_DOCUMENTO
                    ,F.NRO_FACTURA NRO_FACTURA_FA
                    ,IIC.TOTAL_NETO
                    ,IIC.COD_DOC
                    ,MONTO_COMISION
                    ,CONVERT(VARCHAR, F.FECHA_FACTURA, 103) FECHA_FACTURA
                    ,F.COD_DOC COD_NOTA_VENTA
                    ,'S' SELECCIONAR
                FROM ITEM_INFORME_COMISION IIC
                    ,FACTURA F
                WHERE COD_INFORME_COMISION = {KEY1}
                AND TIPO_DOCUMENTO = 'FACTURA'
                AND IIC.COD_DOC_DOCUMENTO = F.COD_FACTURA
                ORDER BY NRO_FACTURA ASC";

		parent::datawindow($sql, 'ITEM_INFORME_COMISION_FA');

        $this->add_control(new edit_text('COD_ITEM_INFORME_COMISION',10, 10, 'hidden'));
		$this->add_control(new static_text('NRO_FACTURA_FA'));
		$this->add_control(new static_num('TOTAL_NETO'));
		$this->add_control(new static_num('MONTO_COMISION'));
        $this->add_control($control = new edit_check_box('SELECCIONAR', 'S', 'N'));
        $control->set_onChange("check_nc(this); calcula_totales();");
	}

	function update($db){
		$sp = 'spu_item_informe_comision';
		
		for ($i = 0; $i < $this->row_count(); $i++){
			$SELECCIONAR = $this->get_item($i, 'SELECCIONAR');
			if($SELECCIONAR == 'N')
				continue;

			$COD_ITEM_INFORME_COMISION 	= $this->get_item($i, 'COD_ITEM_INFORME_COMISION');
			$COD_INFORME_COMISION 		= $this->get_item($i, 'COD_INFORME_COMISION');
			$TIPO_DOCUMENTO 			= $this->get_item($i, 'TIPO_DOCUMENTO');
			$COD_DOC_DOCUMENTO 			= $this->get_item($i, 'COD_DOC_DOCUMENTO');
			$TOTAL_NETO 				= $this->get_item($i, 'TOTAL_NETO');
			$COD_DOC 					= $this->get_item($i, 'COD_DOC');
			$MONTO_COMISION 			= $this->get_item($i, 'MONTO_COMISION');

			$COD_ITEM_INFORME_COMISION	= ($COD_ITEM_INFORME_COMISION =='') ? "null" : $COD_ITEM_INFORME_COMISION;
					
			$param = "'INSERT'
					 ,$COD_ITEM_INFORME_COMISION
					 ,$COD_INFORME_COMISION
					 ,$TIPO_DOCUMENTO
					 ,$COD_DOC_DOCUMENTO
					 ,$TOTAL_NETO
					 ,$COD_DOC
					 ,$MONTO_COMISION";
			
			if (!$db->EXECUTE_SP($sp, $param))
				return false;		
		}
	
		return true;
	}
}

class dw_item_informe_comision_nc extends datawindow{
	function dw_item_informe_comision_nc(){
		$sql = "SELECT COD_ITEM_INFORME_COMISION	                COD_ITEM_INFORME_COMISION_NC
                    ,COD_INFORME_COMISION	                        COD_INFORME_COMISION_NC
                    ,TIPO_DOCUMENTO	                                TIPO_DOCUMENTO_NC
                    ,COD_DOC_DOCUMENTO                              COD_DOC_DOCUMENTO_NC
                    ,NC.NRO_NOTA_CREDITO
                    ,IIC.TOTAL_NETO                                 TOTAL_NETO_NC
                    ,IIC.COD_DOC                                    COD_DOC_NC
                    ,MONTO_COMISION                                 MONTO_COMISION_NC
                    ,CONVERT(VARCHAR, NC.FECHA_NOTA_CREDITO, 103)   FECHA_NOTA_CREDITO
                    ,(SELECT NRO_FACTURA 
                      FROM FACTURA WHERE COD_FACTURA = NC.COD_DOC)  NRO_FACTURA_NC
                    ,'S'                                            SELECCIONAR_NC
                    ,'S'                                            SELECCIONAR_NC_H
                FROM ITEM_INFORME_COMISION IIC
                    ,NOTA_CREDITO NC
                WHERE COD_INFORME_COMISION = {KEY1}
                AND TIPO_DOCUMENTO = 'NOTA_CREDITO'
                AND IIC.COD_DOC_DOCUMENTO = NC.COD_NOTA_CREDITO";

		parent::datawindow($sql, 'ITEM_INFORME_COMISION_NC');

        $this->add_control(new edit_text('COD_ITEM_INFORME_COMISION_NC',10, 10, 'hidden'));
		$this->add_control(new static_text('NRO_FACTURA_NC'));
		$this->add_control(new static_num('TOTAL_NETO_NC'));
		$this->add_control(new static_num('MONTO_COMISION_NC'));
        $this->add_control(new edit_check_box('SELECCIONAR_NC', 'S', 'N'));
        $this->set_entrable('SELECCIONAR_NC', false);
        $this->add_control(new edit_text('SELECCIONAR_NC_H',10, 10, 'hidden'));
	}

	function update($db){
        $sp = 'spu_item_informe_comision';
		
		for ($i = 0; $i < $this->row_count(); $i++){
			$SELECCIONAR = $this->get_item($i, 'SELECCIONAR_NC_H');
			if($SELECCIONAR == 'N')
				continue;

			$COD_ITEM_INFORME_COMISION 	= $this->get_item($i, 'COD_ITEM_INFORME_COMISION_NC');
			$COD_INFORME_COMISION 		= $this->get_item($i, 'COD_INFORME_COMISION_NC');
			$TIPO_DOCUMENTO 			= $this->get_item($i, 'TIPO_DOCUMENTO_NC');
			$COD_DOC_DOCUMENTO 			= $this->get_item($i, 'COD_DOC_DOCUMENTO_NC');
			$TOTAL_NETO 				= $this->get_item($i, 'TOTAL_NETO_NC');
			$COD_DOC 					= $this->get_item($i, 'COD_DOC_NC');
			$MONTO_COMISION 			= $this->get_item($i, 'MONTO_COMISION_NC');

			$COD_ITEM_INFORME_COMISION	= ($COD_ITEM_INFORME_COMISION =='') ? "null" : $COD_ITEM_INFORME_COMISION;
					
			$param = "'INSERT'
					 ,$COD_ITEM_INFORME_COMISION
					 ,$COD_INFORME_COMISION
					 ,$TIPO_DOCUMENTO
					 ,$COD_DOC_DOCUMENTO
					 ,$TOTAL_NETO
					 ,$COD_DOC
					 ,$MONTO_COMISION";
			
			if (!$db->EXECUTE_SP($sp, $param))
				return false;		
		}
	
		return true;
	}
}

class wi_informe_comision extends w_input{
	function wi_informe_comision($cod_item_menu){		
		parent::w_input('informe_comision', $cod_item_menu);

        $sql = "SELECT COD_INFORME_COMISION
                    ,CONVERT(VARCHAR, FECHA_REGISTRO, 103) FECHA_REGISTRO
                    ,COD_ESTADO_INFORME_COMISION
                    ,U.COD_USUARIO
                    ,U.NOM_USUARIO
                    ,REFERENCIA
                    ,IC.COD_EMPRESA
                    ,E.NOM_EMPRESA
                    ,dbo.number_format(E.RUT, 0, ',', '.') RUT
                    ,E.DIG_VERIF
                    ,0 TOTAL_COMISION
                    ,0 T_NETO_FA
                    ,0 T_COMISION_FA
                    ,0 T_NETO_NC
                    ,0 T_COMISION_NC
                    ,'none' DISPLAY_FA
                    ,'none' DISPLAY_NC
                    ,0 FIN_FA
                    ,0 FIN_NC
                FROM INFORME_COMISION IC
                    ,USUARIO U
                    ,EMPRESA E
                WHERE COD_INFORME_COMISION = {KEY1}
                AND U.COD_USUARIO = IC.COD_USUARIO
                AND E.COD_EMPRESA = IC.COD_EMPRESA";

        $this->dws['dw_informe_comision'] = new datawindow($sql);
        $this->dws['dw_item_informe_comision_fa'] = new dw_item_informe_comision_fa();
        $this->dws['dw_item_informe_comision_nc'] = new dw_item_informe_comision_nc();

        $sql = "SELECT COD_ESTADO_INFORME_COMISION
                      ,NOM_ESTADO_INFORME_COMISION
                FROM ESTADO_INFORME_COMISION";
		$this->dws['dw_informe_comision']->add_control(new drop_down_dw('COD_ESTADO_INFORME_COMISION',$sql,150));
        $this->dws['dw_informe_comision']->add_control(new edit_text('REFERENCIA',80, 150));
        $this->dws['dw_informe_comision']->add_control(new static_num('T_NETO_FA'));
        $this->dws['dw_informe_comision']->add_control(new static_num('T_COMISION_FA'));
        $this->dws['dw_informe_comision']->add_control(new static_num('T_NETO_NC'));
        $this->dws['dw_informe_comision']->add_control(new static_num('T_COMISION_NC'));
        $this->dws['dw_informe_comision']->add_control(new static_num('TOTAL_COMISION'));
	}

	function new_record(){
        $cod_empresa_sox = session::get('INF_COMISION');			
		session::un_set('INF_COMISION');

        $this->dws['dw_informe_comision']->insert_row();
        $this->dws['dw_informe_comision']->set_item(0, 'FECHA_REGISTRO', $this->current_date());
		$this->dws['dw_informe_comision']->set_item(0, 'COD_USUARIO', $this->cod_usuario);
		$this->dws['dw_informe_comision']->set_item(0, 'NOM_USUARIO', $this->nom_usuario);
        $this->dws['dw_informe_comision']->set_item(0, 'DISPLAY_FA', '');
        $this->dws['dw_informe_comision']->set_item(0, 'DISPLAY_NC', '');

		$db = new database(K_TIPO_BD, K_SERVER, K_BD, K_USER, K_PASS);
		
		$sql = "SELECT NOM_EMPRESA
                    ,dbo.number_format(RUT, 0, ',', '.') RUT
                    ,DIG_VERIF
                FROM EMPRESA
                WHERE COD_EMPRESA = $cod_empresa_sox";
		$result = $db->build_results($sql);

        $this->dws['dw_informe_comision']->set_item(0, 'COD_EMPRESA', $cod_empresa_sox);
		$this->dws['dw_informe_comision']->set_item(0, 'NOM_EMPRESA', $result[0]['NOM_EMPRESA']);
		$this->dws['dw_informe_comision']->set_item(0, 'DIG_VERIF', $result[0]['DIG_VERIF']);
        $this->dws['dw_informe_comision']->set_item(0, 'RUT', $result[0]['RUT']);
        $this->dws['dw_informe_comision']->set_item(0, 'COD_ESTADO_INFORME_COMISION', 1);
        $this->dws['dw_informe_comision']->set_item(0, 'T_NETO_FA', 0);
        $this->dws['dw_informe_comision']->set_item(0, 'T_COMISION_FA', 0);
        $this->dws['dw_informe_comision']->set_item(0, 'T_NETO_NC', 0);
        $this->dws['dw_informe_comision']->set_item(0, 'T_COMISION_NC', 0);
        $this->dws['dw_informe_comision']->set_item(0, 'TOTAL_COMISION', 0);

        $sql = "SELECT NRO_FACTURA
                      ,CONVERT(VARCHAR, FECHA_FACTURA, 103) FECHA_FACTURA
                      ,COD_DOC
                      ,TOTAL_NETO
                      ,COD_FACTURA
                FROM FACTURA
                WHERE COD_EMPRESA = $cod_empresa_sox
                AND YEAR(FECHA_FACTURA) >= 2024
                AND COD_ESTADO_DOC_SII = 3 --ENVIADA AL SII
                AND COD_FACTURA NOT IN (SELECT COD_DOC_DOCUMENTO
                                        FROM ITEM_INFORME_COMISION IIC
											,INFORME_COMISION IC
                                        WHERE TIPO_DOCUMENTO = 'FACTURA'
										AND COD_ESTADO_INFORME_COMISION in (1, 2)
										AND IIC.COD_INFORME_COMISION = IC.COD_INFORME_COMISION) --EMITIDA y CONFIRMADA
                ORDER BY NRO_FACTURA ASC";

		$result = $db->build_results($sql);
        
        $porc_comision = $this->get_parametro('87');
        $cod_factura_list = "";
        $this->dws['dw_informe_comision']->set_item(0, 'FIN_FA', count($result));

        for($i=0; $i < count($result); $i++){
            $cod_factura_list .= $result[$i]['COD_FACTURA'].',';
            $this->dws['dw_item_informe_comision_fa']->insert_row();

            $monto_comision = $result[$i]['TOTAL_NETO'] * ($porc_comision/100);
            $this->dws['dw_item_informe_comision_fa']->set_item($i, 'NRO_FACTURA_FA', $result[$i]['NRO_FACTURA']);
			$this->dws['dw_item_informe_comision_fa']->set_item($i, 'COD_DOC_DOCUMENTO', $result[$i]['COD_FACTURA']);
			$this->dws['dw_item_informe_comision_fa']->set_item($i, 'TIPO_DOCUMENTO', 'FACTURA');
			$this->dws['dw_item_informe_comision_fa']->set_item($i, 'COD_DOC', $result[$i]['COD_DOC']);
            $this->dws['dw_item_informe_comision_fa']->set_item($i, 'FECHA_FACTURA', $result[$i]['FECHA_FACTURA']);
            $this->dws['dw_item_informe_comision_fa']->set_item($i, 'COD_NOTA_VENTA', $result[$i]['COD_DOC']);
            $this->dws['dw_item_informe_comision_fa']->set_item($i, 'TOTAL_NETO', $result[$i]['TOTAL_NETO']);
            $this->dws['dw_item_informe_comision_fa']->set_item($i, 'MONTO_COMISION', $monto_comision);
            $this->dws['dw_item_informe_comision_fa']->set_item($i, 'SELECCIONAR', 'N');
        }
        $cod_factura_list	= trim($cod_factura_list, ",");
        $cod_factura_list	= ($cod_factura_list =='') ? "null" : $cod_factura_list;
        
        $sql = "SELECT NRO_NOTA_CREDITO
                      ,CONVERT(VARCHAR, FECHA_NOTA_CREDITO, 103) FECHA_NOTA_CREDITO
                      ,N.COD_DOC
                      ,(SELECT NRO_FACTURA 
                      FROM FACTURA WHERE COD_FACTURA = N.COD_DOC)  NRO_FACTURA_NC
                      ,N.TOTAL_NETO
                      ,COD_NOTA_CREDITO
                      ,F.COD_DOC COD_NOTA_VENTA
                FROM NOTA_CREDITO N
					,FACTURA F
                WHERE N.COD_DOC in ($cod_factura_list)
                AND YEAR(FECHA_NOTA_CREDITO) >= 2024
                AND N.COD_ESTADO_DOC_SII = 3 --ENVIADA AL SII
				AND N.COD_DOC = F.COD_FACTURA
                ORDER BY NRO_NOTA_CREDITO ASC";

		$result = $db->build_results($sql);
        $this->dws['dw_informe_comision']->set_item(0, 'FIN_NC', count($result));

        for($i=0; $i < count($result); $i++){
            $this->dws['dw_item_informe_comision_nc']->insert_row();

            $monto_comision = $result[$i]['TOTAL_NETO'] * ($porc_comision/100);
            $this->dws['dw_item_informe_comision_nc']->set_item($i, 'NRO_NOTA_CREDITO', $result[$i]['NRO_NOTA_CREDITO']);
            $this->dws['dw_item_informe_comision_nc']->set_item($i, 'COD_DOC_DOCUMENTO_NC', $result[$i]['COD_NOTA_CREDITO']);
			$this->dws['dw_item_informe_comision_nc']->set_item($i, 'TIPO_DOCUMENTO_NC', 'NOTA_CREDITO');
			$this->dws['dw_item_informe_comision_nc']->set_item($i, 'COD_DOC_NC', $result[$i]['COD_NOTA_VENTA']);
            $this->dws['dw_item_informe_comision_nc']->set_item($i, 'FECHA_NOTA_CREDITO', $result[$i]['FECHA_NOTA_CREDITO']);
            $this->dws['dw_item_informe_comision_nc']->set_item($i, 'NRO_FACTURA_NC', $result[$i]['NRO_FACTURA_NC']);
            $this->dws['dw_item_informe_comision_nc']->set_item($i, 'TOTAL_NETO_NC', $result[$i]['TOTAL_NETO']);
            $this->dws['dw_item_informe_comision_nc']->set_item($i, 'MONTO_COMISION_NC', $monto_comision);
            $this->dws['dw_item_informe_comision_nc']->set_item($i, 'SELECCIONAR_NC', 'N');
            $this->dws['dw_item_informe_comision_nc']->set_item($i, 'SELECCIONAR_NC_H', 'N');
        }
	}

	function load_record(){
		$cod_informe_comision = $this->get_item_wo($this->current_record, 'COD_INFORME_COMISION');
		$this->dws['dw_informe_comision']->retrieve($cod_informe_comision);
		$this->dws['dw_item_informe_comision_fa']->retrieve($cod_informe_comision);
        $this->dws['dw_item_informe_comision_nc']->retrieve($cod_informe_comision);
        $this->dws['dw_item_informe_comision_fa']->set_entrable('SELECCIONAR', false);

        //tab factura
        $T_NETO_FA      = 0;
        $T_COMISION_FA  = 0;
        for ($i=0; $i < $this->dws['dw_item_informe_comision_fa']->row_count() ; $i++) { 
            $total_neto     = $this->dws['dw_item_informe_comision_fa']->get_item($i, 'TOTAL_NETO');
            $monto_comision = $this->dws['dw_item_informe_comision_fa']->get_item($i, 'MONTO_COMISION');

            $T_NETO_FA += $total_neto;
            $T_COMISION_FA += $monto_comision;
        }

        $this->dws['dw_informe_comision']->set_item(0, 'T_NETO_FA', $T_NETO_FA);
        $this->dws['dw_informe_comision']->set_item(0, 'T_COMISION_FA', $T_COMISION_FA);

        //tab nota credito
        $T_NETO_NC      = 0;
        $T_COMISION_NC  = 0;
        for ($i=0; $i < $this->dws['dw_item_informe_comision_nc']->row_count() ; $i++) { 
            $total_neto     = $this->dws['dw_item_informe_comision_nc']->get_item($i, 'TOTAL_NETO_NC');
            $monto_comision = $this->dws['dw_item_informe_comision_nc']->get_item($i, 'MONTO_COMISION_NC');

            $T_NETO_NC += $total_neto;
            $T_COMISION_NC += $monto_comision;
        }

        $this->dws['dw_informe_comision']->set_item(0, 'T_NETO_NC', $T_NETO_NC);
        $this->dws['dw_informe_comision']->set_item(0, 'T_COMISION_NC', $T_COMISION_NC);


        $total_comision = $T_COMISION_FA - $T_COMISION_NC;
        $this->dws['dw_informe_comision']->set_item(0, 'TOTAL_COMISION', $total_comision);

        $cod_estado_ic = $this->dws['dw_informe_comision']->get_item(0, 'COD_ESTADO_INFORME_COMISION');

        if($cod_estado_ic == 1){
            $this->b_no_save_visible = true;
            $this->b_save_visible 	 = true;
            $this->b_modify_visible  = true;
        }else{
            $this->b_no_save_visible = false;
            $this->b_save_visible 	 = false;
            $this->b_modify_visible  = false;
        }
	}
	
	function get_key(){
		return $this->dws['dw_informe_comision']->get_item(0, 'COD_INFORME_COMISION');
	}	
	
	function save_record($db){
		$db = new database(K_TIPO_BD, K_SERVER, K_BD, K_USER, K_PASS);
		$cod_informe_comision			= $this->get_key();
		$cod_estado_informe_comision	= $this->dws['dw_informe_comision']->get_item(0, 'COD_ESTADO_INFORME_COMISION');
		$cod_usuario 					= $this->dws['dw_informe_comision']->get_item(0, 'COD_USUARIO');
		$cod_empresa					= $this->dws['dw_informe_comision']->get_item(0, 'COD_EMPRESA');
		$referencia						= $this->dws['dw_informe_comision']->get_item(0, 'REFERENCIA');

		$cod_informe_comision			= ($cod_informe_comision =='') ? "null" : "$cod_informe_comision";
		$referencia						= ($referencia =='') ? "null" : "'$referencia'";

		$sp = 'spu_informe_comision';
	    if ($this->is_new_record())
	    	$operacion = 'INSERT';
	    else
	    	$operacion = 'UPDATE';

		$param = "'$operacion'
				,$cod_informe_comision
				,$cod_estado_informe_comision
				,$cod_usuario
				,$cod_empresa
				,$referencia";
		
		if($db->EXECUTE_SP($sp, $param)){
			if($this->is_new_record()){
				$cod_informe_comision = $db->GET_IDENTITY();
				$this->dws['dw_informe_comision']->set_item(0, 'COD_INFORME_COMISION', $cod_informe_comision);

                for ($i=0; $i<$this->dws['dw_item_informe_comision_fa']->row_count(); $i++)
                    $this->dws['dw_item_informe_comision_fa']->set_item($i, 'COD_INFORME_COMISION', $cod_informe_comision);

                if (!$this->dws['dw_item_informe_comision_fa']->update($db))
                    return false;
                
                for ($i=0; $i<$this->dws['dw_item_informe_comision_nc']->row_count(); $i++)
                    $this->dws['dw_item_informe_comision_nc']->set_item($i, 'COD_INFORME_COMISION_NC', $cod_informe_comision);

                if (!$this->dws['dw_item_informe_comision_nc']->update($db))
                    return false;
			}

			return true;			
		}
		return false;
	}
}
?>