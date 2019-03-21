<?php

require_once 'Base.class.php';

class Type_VirtQuotaAcct extends Type_Base {

	function rrd_gen_graph() {
		$rrdgraph = $this->rrd_options();

		$sources = $this->rrd_get_sources();

		$i=0;
		foreach ($this->tinstances as $tinstance) {
			foreach ($this->data_sources as $ds) {
				$rrdgraph[] = sprintf('DEF:%s=%s:%s:AVERAGE', $sources[$i], $this->parse_filename($this->files[$tinstance]), $ds);
				$rrdgraph[] = sprintf('DEF:max_%s=%s:%s:MAX', $sources[$i], $this->parse_filename($this->files[$tinstance]), $ds);
				$i++;
			}
		}
#		$strpos_n = $last_space_position = strrpos($this->parse_filename($this->files[$tinstance]), '/');
		$exploded_path = explode('/',$this->parse_filename($this->files[$tinstance]));
		$rrd_dir = implode('/',array_slice($exploded_path,0,count($exploded_path)-1));
		$last_subdir_exp = explode('-',$exploded_path[count($exploded_path)-2]); 
		$cgroup_dir = implode('/',array_slice($exploded_path,0,count($exploded_path)-2)).'/CGroup-'.$last_subdir_exp[1].' vCPU';
#		$rrd_dir =  substr($this->parse_filename($this->files[$tinstance]), 0, $strpos_n);
		$rrdgraph[] = sprintf('DEF:vcpu_quota=%s/gauge-vcpu_quota.rrd:value:AVERAGE', $rrd_dir);
		if (file_exists("$cgroup_dir/gauge-vcpu_hard_quota.rrd")) {
			$rrdgraph[] = sprintf('DEF:vcpu_hard_quota=%s/gauge-vcpu_hard_quota.rrd:value:AVERAGE', $cgroup_dir);
		}
		$cdef_args = 'vcpu_1,';
		for ($i=count($sources)-1 ; $i>=0 ; $i--) {
				if (strpos($sources[$i], 'cpu') && $sources[$i] != 'vcpu_1') {
					$cdef_args .= $sources[$i].',+,';
				}
		}
		//var_dump($cdef_args);die();
		$cdef_args .= '10000,/';
		$cdef_args .= ','.(count($sources)-1).',/';
		$rrdgraph[] = sprintf('CDEF:cpuacct=%s', $cdef_args);
		$rrdgraph[] = sprintf('LINE1:cpuacct#%s:%s', $this->validate_color($color), $this->rrd_escape($this->legend['cpuacct']));
		$rrdgraph[] = 'GPRINT:cpuacct:LAST:%5.1lf%s Last\l';
		$rrdgraph[] = 'LINE1:vcpu_quota#ff0000:VCPU QUOTA';
		$rrdgraph[] = 'GPRINT:vcpu_quota:LAST:%5.1lf%s Last\l';
		$rrdgraph[] = 'LINE1:vcpu_hard_quota#ff0000:VCPU hard QUOTA';
		$rrdgraph[] = 'GPRINT:vcpu_hard_quota:LAST:%5.1lf%s Last\l';
//		echo "<pre>";var_dump($sources);var_dump($rrdgraph);die();
		return $rrdgraph;
	}
}
