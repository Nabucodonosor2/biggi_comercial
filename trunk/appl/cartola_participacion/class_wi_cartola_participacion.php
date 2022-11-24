<?php
require_once(dirname(__FILE__)."/../../../../commonlib/trunk/php/auto_load.php");

class wi_cartola_participacion extends w_input {
    
	function wi_cartola_participacion($cod_item_menu) {
		parent::w_input('cartola_participacion', $cod_item_menu);

		$sql = "select CP.COD_CARTOLA_PARTICIPACION
                        , CP.COD_USUARIO COD_USUARIO_VENDEDOR_H
                         ,NOM_USUARIO NOM_USUARIO_VENDEDOR
                         ,(  select top 1 saldo  from ITEM_CARTOLA_PARTICIPACION 
                                WHERE COD_CARTOLA_PARTICIPACION={KEY1}
                                ORDER BY COD_ITEM_CARTOLA_PARTICIPACION DESC) SALDO_FINAL
                        ,'none' VISIBLE
						,CP.ANO_CARTOLA
						,CP.COD_CARTOLA_ANTERIOR
						,CP.SALDO_CARTOLA_ANTERIOR
                 from CARTOLA_PARTICIPACION CP , USUARIO U
                 where CP.COD_USUARIO=U.COD_USUARIO
                 and COD_CARTOLA_PARTICIPACION={KEY1}";
		
		$this->dws['dw_cartola_participacion'] = new datawindow($sql);
		
		$this->dws['dw_cartola_participacion']->add_control(new edit_text('COD_USUARIO_VENDEDOR_H',10,10,'hidden'));
		//$this->dws['dw_cartola_participacion']->add_control(new static_text('VISIBLE',10,10));
		$this->dws['dw_cartola_participacion']->add_control(new edit_num('SALDO_FINAL',10,10));
		$this->dws['dw_cartola_participacion']->add_control(new static_num('SALDO_CARTOLA_ANTERIOR'));

		
		/////////////		dw_item_cartola_participacion
		$sql = "set LANGUAGE Spanish select COD_ITEM_CARTOLA_PARTICIPACION
                        ,COD_CARTOLA_PARTICIPACION
                        ,[TIPO_MOVIMIENTO]
                        ,CASE
                         WHEN TIPO_MOVIMIENTO ='ABONO' THEN MONTO
                        ELSE 0
                        END AS ABONO
                        ,CASE
                         WHEN TIPO_MOVIMIENTO ='ABONO' THEN MONTO
                        ELSE 0
                        END AS ABONO_H
                        ,CASE
                         WHEN TIPO_MOVIMIENTO ='RETIRO' THEN MONTO
                        ELSE 0
                        END AS RETIRO
                        ,CASE
                         WHEN TIPO_MOVIMIENTO ='RETIRO' THEN MONTO
                        ELSE 0
                        END AS RETIRO_H
                        ,ICP.COD_PARTICIPACION
                        ,convert(varchar(20),FECHA_MOVIMIENTO, 103) FECHA_MOVIMIENTO 
                        ,convert(varchar(20),FECHA_MOVIMIENTO, 103) FECHA_MOVIMIENTO_H                       
                        ,GLOSA
                        ,GLOSA GLOSA_H
                        ,MONTO
                        ,MONTO MONTO_H
                        ,ICP.COD_USUARIO
                        ,(SELECT  NOM_USUARIO FROM USUARIO WHERE COD_USUARIO=ICP.COD_USUARIO )     NOM_USUARIO
                        ,p.COD_USUARIO_VENDEDOR
                        ,NOM_USUARIO NOM_USUARIO_VENDEDOR
                        ,SALDO
                        ,SALDO  SALDO_H
                        ,UPPER(DateName(month,fecha_movimiento)) MES
                from  ITEM_CARTOLA_PARTICIPACION ICP 
                left JOIN PARTICIPACION P ON ICP.COD_PARTICIPACION=P.COD_PARTICIPACION
                 left JOIN USUARIO U ON P.COD_USUARIO_VENDEDOR=U.COD_USUARIO
                where COD_CARTOLA_PARTICIPACION={KEY1} 
                ORDER by COD_ITEM_CARTOLA_PARTICIPACION ASC";
		
		$this->dws['dw_item_cartola_participacion'] = new datawindow($sql ,'ITEM_CARTOLA_PARTICIPACION');
		

		// asigna los formatos
		$this->dws['dw_item_cartola_participacion']->add_control(new static_text('FECHA_MOVIMIENTO'));	
		$this->dws['dw_item_cartola_participacion']->add_control(new static_text('GLOSA'));
		$this->dws['dw_item_cartola_participacion']->add_control(new static_num('ABONO'));
		$this->dws['dw_item_cartola_participacion']->add_control(new static_num('RETIRO'));
		$this->dws['dw_item_cartola_participacion']->add_control(new static_num('SALDO'));
		
		$this->dws['dw_item_cartola_participacion']->add_control(new edit_text('COD_ITEM_CARTOLA_PARTICIPACION',10,10,'hidden'));
		$this->dws['dw_item_cartola_participacion']->add_control(new edit_text('FECHA_MOVIMIENTO_H',10,10,'hidden'));
		$this->dws['dw_item_cartola_participacion']->add_control(new edit_text('GLOSA_H',10,10,'hidden'));
		$this->dws['dw_item_cartola_participacion']->add_control(new edit_text('ABONO_H',10,10,'hidden'));
		$this->dws['dw_item_cartola_participacion']->add_control(new edit_text('RETIRO_H',10,10,'hidden'));
		$this->dws['dw_item_cartola_participacion']->add_control(new edit_text('SALDO_H',10,10,'hidden'));
		$this->dws['dw_item_cartola_participacion']->add_control(new edit_text('COD_PARTICIPACION',10,10,'hidden'));
		$this->dws['dw_item_cartola_participacion']->add_control(new static_text('MES'));
		
		// mandatory
		$this->dws['dw_item_cartola_participacion']->set_mandatory('GLOSA', 'Glosa');
		$this->dws['dw_item_cartola_participacion']->set_mandatory('ABONO', 'Abono');
		$this->dws['dw_item_cartola_participacion']->set_mandatory('RETIRO', 'Retiro');
		$this->dws['dw_item_cartola_participacion']->set_mandatory('SALDO', 'Saldo');
		///////////////	dw_detalle_cartola_participacion
		
		$sql_d = "select COD_ITEM_CARTOLA_PARTICIPACION COD_ITEM_CARTOLA_PART_D
                        ,COD_CARTOLA_PARTICIPACION COD_CARTOLA_PARTICIPACION_D
                        ,[TIPO_MOVIMIENTO] TIPO_MOVIMIENTO_D
                        ,CASE
                         WHEN TIPO_MOVIMIENTO ='ABONO' THEN MONTO
                        ELSE 0
                        END AS ABONO_D
                        ,CASE
                         WHEN TIPO_MOVIMIENTO ='RETIRO' THEN MONTO
                        ELSE 0
                        END AS RETIRO_D
                        ,ICP.COD_PARTICIPACION  COD_PARTICIPACION_D
                        ,convert(varchar(20),FECHA_MOVIMIENTO, 103) FECHA_MOVIMIENTO_D
                        ,GLOSA GLOSA_D
                        ,MONTO  MONTO_D
                        ,ICP.COD_USUARIO COD_USUARIO_D
                        ,(SELECT  NOM_USUARIO FROM USUARIO WHERE COD_USUARIO=ICP.COD_USUARIO )     NOM_USUARIO_D
                        ,p.COD_USUARIO_VENDEDOR COD_USUARIO_VENDEDOR_D
                        ,NOM_USUARIO NOM_USUARIO_VENDEDOR_D
		              
                from  ITEM_CARTOLA_PARTICIPACION ICP
                left JOIN PARTICIPACION P ON ICP.COD_PARTICIPACION=P.COD_PARTICIPACION
                 left JOIN USUARIO U ON P.COD_USUARIO_VENDEDOR=U.COD_USUARIO
                where COD_CARTOLA_PARTICIPACION={KEY1} 
                ORDER by COD_ITEM_CARTOLA_PARTICIPACION ASC ";
		
		$this->dws['dw_detalle_cartola_participacion'] = new datawindow($sql_d ,'DETALLE_CARTOLA_PARTICIPACION');
				
		// asigna los formatos
		$this->dws['dw_detalle_cartola_participacion']->add_control(new static_text('COD_ITEM_CARTOLA_PARTICIPACION_D'));
		$this->dws['dw_detalle_cartola_participacion']->add_control(new static_text('COD_CARTOLA_PARTICIPACION_D'));
		$this->dws['dw_detalle_cartola_participacion']->add_control(new static_text('TIPO_MOVIMIENTO_D'));
		$this->dws['dw_detalle_cartola_participacion']->add_control(new static_text('FECHA_MOVIMIENTO_D'));
		$this->dws['dw_detalle_cartola_participacion']->add_control(new static_text('GLOSA_D'));
		$this->dws['dw_detalle_cartola_participacion']->add_control(new static_num('MONTO_D'));
		$this->dws['dw_detalle_cartola_participacion']->add_control(new static_text('COD_PARTICIPACION_D'));
		$this->dws['dw_detalle_cartola_participacion']->add_control(new static_text('NOM_USUARIO_D'));
		
	}
		
	function new_record() {
		$this->dws['dw_cartola_participacion']->insert_row();
		
	}

	function load_record() {
	    
	    $COD_CARTOLA_PARTICIPACION = $this->get_item_wo($this->current_record, 'COD_CARTOLA_PARTICIPACION');
	    $this->dws['dw_cartola_participacion']->retrieve($COD_CARTOLA_PARTICIPACION);
	    $this->dws['dw_detalle_cartola_participacion']->retrieve($COD_CARTOLA_PARTICIPACION);
	    $this->dws['dw_item_cartola_participacion']->retrieve($COD_CARTOLA_PARTICIPACION);
	    
	    $this->dws['dw_cartola_participacion']->set_entrable('SALDO_FINAL', false);
	   	

	    
	    if($this->modify == true)	        
	        $this->dws['dw_cartola_participacion']->set_item( 0,'VISIBLE', '');

	}

	function get_key() {
		return $this->dws['dw_cartola_participacion']->get_item(0, 'COD_CARTOLA_PARTICIPACION');
	}

	function save_record($db) {
	    $COD_CARTOLA_PARTICIPACION=$this->get_key();
	    $item=$this->dws['dw_item_cartola_participacion']->row_count();
	    $sp='spu_item_cartola';
	    
	    for ($i = 0; $i < $item; $i++){
                
	        $fecha_movimiento                  = $this->dws['dw_item_cartola_participacion']->get_item($i, 'FECHA_MOVIMIENTO_H');
	        $cod_item_cartola_participacion    = $this->dws['dw_item_cartola_participacion']->get_item($i, 'COD_ITEM_CARTOLA_PARTICIPACION');
	        $cod_participacion                 = $this->dws['dw_item_cartola_participacion']->get_item($i, 'COD_PARTICIPACION');
	        $glosa                             = $this->dws['dw_item_cartola_participacion']->get_item($i, 'GLOSA_H');
	        $abono                             = $this->dws['dw_item_cartola_participacion']->get_item($i, 'ABONO_H');
	        $abono=str_replace ( '.' , '' , $abono  );
	        $retiro                            = $this->dws['dw_item_cartola_participacion']->get_item($i, 'RETIRO_H');
	        $retiro=str_replace ( '.' , '' , $retiro  );
	        $saldo                             = $this->dws['dw_item_cartola_participacion']->get_item($i, 'SALDO_H');
	        $saldo=str_replace ( '.' , '' , $saldo  );
	        
	        
            $cod_item_cartola_participacion = ($cod_item_cartola_participacion =='') ? "null" : "$cod_item_cartola_participacion";
            $cod_participacion = ($cod_participacion =='') ? "null" : "$cod_participacion";
            $fecha_movimiento = ($fecha_movimiento =='') ? "null" : $this->str2date($fecha_movimiento);
            $saldo = ($saldo =='') ? 0 : "$saldo";
            
            
            if ($retiro>0) {
                $fecha_movimiento="null";
            }
             $param = "'INSERT'
            ,$cod_item_cartola_participacion
            ,$COD_CARTOLA_PARTICIPACION
            ,$cod_participacion
			,$fecha_movimiento
			,'$glosa'
			,$abono
			,$retiro
            ,$this->cod_usuario
             ,$saldo";
             
             if($cod_item_cartola_participacion=='null'){ 
                   if (!$db->EXECUTE_SP($sp, $param)){
                     return false;
                     
                  }else{
                     $cod_item_cartola_participacion = $db->GET_IDENTITY();                 
                    
                  } 
             }               
        }	
        return true;
	}
	
	function print_record() {
	    $COD_CARTOLA_PARTICIPACION = $this->get_key();
	    print " <script>window.open('print_cartola.php?COD_CARTOLA_PARTICIPACION=$COD_CARTOLA_PARTICIPACION')</script>";
	    $this->redraw();
	}
}
?>