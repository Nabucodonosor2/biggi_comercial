<?php
class wo_producto extends wo_producto_base{
	const K_BODEGA_TERMINADO = 1;
	
	function wo_producto(){
		// Es igual al BASE, solo cambia elk sql donde se agrega stock
		$sql = "select	P.COD_PRODUCTO
						,NOM_PRODUCTO
						,PRECIO_VENTA_PUBLICO
						,NOM_TIPO_PRODUCTO
						,dbo.f_bodega_stock(P.COD_PRODUCTO, ".self::K_BODEGA_TERMINADO.", getdate()) STOCK
						,M.NOM_MARCA
			from 		PRODUCTO P
						,TIPO_PRODUCTO TP
						,MARCA M
			where		P.COD_TIPO_PRODUCTO = TP.COD_TIPO_PRODUCTO
						AND dbo.f_prod_valido (COD_PRODUCTO) = 'S'
						AND P.COD_MARCA = M.COD_MARCA 
			order by 	COD_PRODUCTO";
			
		parent::w_output('producto', $sql, $_REQUEST['cod_item_menu']);

		// headers
		$this->add_header(new header_modelo('COD_PRODUCTO', 'P.COD_PRODUCTO', 'Modelo'));
		$this->add_header(new header_text('NOM_PRODUCTO', 'NOM_PRODUCTO', 'Descripción'));
		$this->dw->add_control(new edit_precio('PRECIO_VENTA_PUBLICO'));
		$this->add_header(new header_num('PRECIO_VENTA_PUBLICO', 'PRECIO_VENTA_PUBLICO', 'Precio'));
		
		$sql="SELECT COD_MARCA 
 				,NOM_MARCA
 				FROM MARCA
 			ORDER  BY NOM_MARCA";
		$this->add_header(new header_drop_down('NOM_MARCA', 'P.COD_MARCA', 'Marca',$sql));
		
		$sql_tipo_producto = "select COD_TIPO_PRODUCTO ,NOM_TIPO_PRODUCTO from TIPO_PRODUCTO order by	ORDEN";
		$this->add_header($header = new header_drop_down('NOM_TIPO_PRODUCTO', 'TP.COD_TIPO_PRODUCTO', 'Tipo Producto', $sql_tipo_producto));
		$this->add_header(new header_num('STOCK', "dbo.f_bodega_stock(P.COD_PRODUCTO, ".self::K_BODEGA_TERMINADO.", getdate())", 'Stock'));

		// formatos de columnas
		$this->dw->add_control(new edit_num('PRECIO_VENTA_INTERNO'));
	}
}

?>