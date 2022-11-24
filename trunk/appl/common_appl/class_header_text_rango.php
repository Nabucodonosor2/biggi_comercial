<?php
class header_text_rango extends  header_text {
	public	$valor_filtro2='';

	function header_text_rango($field, $field_bd, $nom_header) {
		parent::header_text($field, $field_bd, $nom_header);
	}
	function make_java_script() {
		return '"return dlg_find_text_rango(\''.$this->nom_header.'\', \''.$this->valor_filtro.'\', \''.$this->valor_filtro2.'\', this);"';		
	}
	
	function set_value_filtro($valor_filtro) {
		if ($valor_filtro == '__BORRAR_FILTRO__'){
			$this->valor_filtro = '';
			$this->valor_filtro2 = '';
		}else if(strrpos($valor_filtro, ',') <> ''){
			$this->valor_filtro = '';
			$this->valor_filtro2 = $valor_filtro;
		}else{
			$this->valor_filtro = $valor_filtro;
			$this->valor_filtro2 = '';
		}	
	}
	
	function draw_header(&$temp, $field_sort='') {
		if ($this->field==$field_sort) 
			$nom_header = "<u>".$this->nom_header."</u>";
		else 
			$nom_header = $this->nom_header;
		
		if ($this->sorteable)
			$onclik = ' onclick="set_order(\''.$this->field.'\')"'; 
		else
			$onclik = ''; 
		
		$html = '<table width="100%"><tr><td class="encabezado_center" align="center" width="95%"><label '.$onclik.'";>'.$nom_header.'</label></td><td align="right"><input id="b_header_'.$this->field.'" type="button" name="b_header_'.$this->field.'"';
		$html .= ' onclick='.$this->make_java_script();
		if (strlen($this->valor_filtro) == 0 && strlen($this->valor_filtro2) == 0)
			$html .= ' src="../../../../commonlib/trunk/images/off_filter.jpg" style="background-image:url(../../../../commonlib/trunk/images/off_filter.jpg); border-color:#919191; border-style: solid; cursor: pointer; background-repeat:no-repeat;background-position:center;"/></td></tr></table>';
		else
			$html .= ' src="../../../../commonlib/trunk/images/on_filter.jpg" style="background-image:url(../../../../commonlib/trunk/images/on_filter.jpg); border-color:#919191; border-style: solid; cursor: pointer; background-repeat:no-repeat;background-position:center;"/></td></tr></table>';
		$temp->setVar('H_'.$this->field, $html);
	}
	
	function make_filtro(){
		if ($this->valor_filtro=='' && $this->valor_filtro2=='')
			return '';
		
		if($this->valor_filtro<>'' && $this->valor_filtro2==''){
			$res = explode('|', $this->valor_filtro);
			$valor = $res[0];
			if ($res[1]=='S')
				// busqueda exacta
				return "(Upper(".$this->field_bd.") like '".strtoupper($valor)."') and ";		
			else
				// comienza por 	
				return "(Upper(".$this->field_bd.") like '".strtoupper($valor)."%') and ";
		}else{
			$array = explode(',', $this->valor_filtro2);
			$lista_producto = "";
			
			for($i=0 ; $i < count($array) ; $i++){
				$cod_producto = $array[$i];
				
				$lista_producto .= "'$cod_producto',";
			}
			
			$lista_producto = substr($lista_producto ,0, strlen($lista_producto)-1);
			
			return "(Upper(".$this->field_bd.") in ($lista_producto)) and ";
		}
	}
}
?>