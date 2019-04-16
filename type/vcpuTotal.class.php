<?php

require_once 'Base.class.php';

class Type_VcpuTotal extends Type_Base {

	function rrd_gen_graph() {
		$rrdgraph = $this->rrd_options();

		$sources = array('value', 'cpu_quota', 'cpu_hard_quota');	
		$name = array('CPU usage', 'CPU burst quota', 'CPU quota');
		$color = array('0000ff', 'ff0000', '00ff00');
		$i = 0;
		foreach ($sources as $ds) {
			$rrdgraph[] = sprintf('DEF:%s=%s:%s:AVERAGE', $ds, $this->parse_filename($this->files['value']), $ds);
			$rrdgraph[] = sprintf('CDEF:%s_percent=%1$s,0.0001,*', $ds);
			$rrdgraph[] = sprintf('LINE%u:%s_percent#%s:%s', $i + 1, $ds, $color[$i], $name[$i]);
			$rrdgraph[] = sprintf('GPRINT:%s_percent:MIN:%%3.2lf%%s%%%% Min', $ds);
			$rrdgraph[] = sprintf('GPRINT:%s_percent:MAX:%%3.2lf%%s%%%% Max', $ds);
			$rrdgraph[] = sprintf('GPRINT:%s_percent:AVERAGE:%%3.2lf%%s%%%% Avg', $ds);
			$rrdgraph[] = sprintf('GPRINT:%s_percent:LAST:%%3.2lf%%s%%%% Last\l', $ds);
			$i++;
		}
		
		return $rrdgraph;	
	}
}
