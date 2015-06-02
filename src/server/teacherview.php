<?php
require_once("../../config.php");
require_once($CFG->libdir . '/completionlib.php');

$id = required_param('id', PARAM_INT);
$nbPage = optional_param('page', '0', PARAM_INT);
$perPage = optional_param('perpage', '1', PARAM_INT);
$url = new moodle_url('/mod/elang/teacherview.php', array('id'=>$id));
if (! $cm = get_coursemodule_from_id('elang', $id)) {
    print_error('invalidcoursemodule');
}

if (! $course = $DB->get_record("course", array("id" => $cm->course))) {
    print_error('coursemisconf');
}
if (!$elang = $DB->get_record('elang', array('id' => $cm->instance), '*', MUST_EXIST)) {
    print_error('invalidcoursemodule');
}

$context = context_module::instance($cm->id);

// Test if teacher: getting the id
$roleteach = get_user_roles($context, $USER->id, true);
$rteach = key($roleteach);
$teachid = $roleteach[$rteach]->roleid;

// Redirect if not teacher
if ($teachid != 3){
	header('Location: /mod/elang/view.php?id='.$id);
}
// Verify access right
require_capability('mod/elang:view', $context);

$PAGE->set_context($context);
$PAGE->set_url($url);
$PAGE->set_heading($course->fullname);
$title = "Teacher: ".$elang->name;

?>
<html<?php echo get_html_lang(); ?>>
	<head>
		<!--<meta charset="utf-8">-->
		<meta http-equiv="X-UA-Compatible" content="IE=edge,chrome=1">
		<meta name="viewport" content="width=device-width, initial-scale=1.0, maximum-scale=1.0, user-scalable=no"/>

		<title><?php echo format_string($title); ?></title>
		<style>
			hr{width:65%;}
			#exportCSV{
				background:#00BF26;
				margin-left:20%;
				border-radius:2px;
				color:white;
				display:inline;
				padding: 5px 10px 5px 10px;
			}
			.etulang{
				background:#FFFFFF;
				width:24%;
				margin-left:25%;
				display:inline-block;				
				border-bottom: rgba(0,0,0,0.7) solid 1px;
				padding-top:6px;
				padding-bottom:1px;
				padding-left:3px;

			}
			.etulang a{color:black; text-decoration:none;}
			.etulang a:hover{color:black; text-decoration:none;}
			.etulang a:focus{color:black; text-decoration:none;}
			.etulang a:active{color:black; text-decoration:none;}
			.etulang a:visited{color:black; text-decoration:none;}
			.etulang:last-child{
				
			}
			.gradeslang{
				display: inline-block;
				margin-right:25%;
				vertical-align:top;
				width:25%;
				text-align:center;
				padding-top:4px;
				padding-bottom:2px;
				border-bottom: rgba(0,0,0,0.7) solid 1px;
				color:white;
				font-weight:bold;
			}
			.errlang{
				background:#E42828;
				border-radius:2px;
				padding:2px;
			}
			.correclang{
				background:#00BF26;
				border-radius:2px;
				padding:2px;
			}
			.helplang{
				border-radius:2px;
				padding:2px;
				background:#38C1C1;
			}
			.paging{
				margin-left:25%;
				width:49.5%;
				word-wrap:break-word;
			}
			.paging a{color:black;}
			
		</style>
		<script src="https://ajax.googleapis.com/ajax/libs/jquery/1.7.2/jquery.min.js"></script>

	</head>
	<body>
	<?php
		echo "<h1>".$elang->name.": overall view</h1>"; 
		echo "<div id='exportCSV'>CSV Export</div>";
		// SOLUTION'S ARRAY
	
		$solutions = $DB->get_records_sql("SELECT id, json FROM mdl_elang_cues WHERE id_elang = ? GROUP BY id", array($cm->instance));
		$tabsol = array();
		foreach($solutions as $key){
			$tempTab=json_decode($key->json, true);
			$tabsol[$key->id]=array();
			foreach($tempTab as $i => $tempKey){		
				if($tempKey['type']=='input'){
					$tabsol[$key->id][$i] = $tempKey['content'];
				}
			}
			if(empty($tabsol[$key->id])){
				unset($tabsol[$key->id]);
			}

		}
	
		
		// STUDENTS'S RECORDS ARRAY
		$essais = $DB->get_records_sql("SELECT * FROM mdl_elang_users WHERE id_elang = ?", array($cm->instance));
		$tabessais = array();
		
		foreach($essais as $key){
			$tabessais[$key->id]["id_cue"] = $key->id_cue;
			$tabessais[$key->id]["id_user"] = $key->id_user;
			$tabessais[$key->id]["json"] = json_decode($key->json, true);
		}
		// DISPLAY
		
		$nomEtu = get_enrolled_users($context, '',0,'*','',$nbPage*$perPage,$perPage);
		echo "<hr>";
		
		function elangStudentsStats($tab, $solutions){
			$etuStats=array();
			foreach($tab as $key){
				if(!isset($etuStats[$key['id_user']])){
					$etuStats[$key['id_user']]=array(0,0,0);
				}
				foreach($key['json'] as $j=>$Kjson){
					if($Kjson['help']){
						$etuStats[$key['id_user']][2]++;
					}else{
						if($solutions[$key['id_cue']][$j]==$Kjson['content']){
							$etuStats[$key['id_user']][1]++;
						}else{
							$etuStats[$key['id_user']][0]++;
						}
					}
				}
			}
			return $etuStats;
		}

		$tabResult=elangStudentsStats($tabessais, $tabsol);
		
		foreach($nomEtu as $key){
			echo "<div class='etulang' id='".$key->id."'><a href='/mod/elang/teacherview_student.php/?id=".$id."&idStd=".$key->id."'>".$key->lastname." ".$key->firstname."</a></div>";
			
			if(isset($tabResult[$key->id])){
				echo "<div class='gradeslang'><span class='errlang'>".$tabResult[$key->id][0]."</span><span class='correclang'>".$tabResult[$key->id][1]."</span><span class='helplang'>".$tabResult[$key->id][2]."</span></div>";			
			}else{
				echo "<div class='gradeslang'><span class='errlang'>0</span><span class='correclang'>0</span><span class='helplang'>0</span></div>";
			}
		}
		echo $OUTPUT->paging_bar(count_enrolled_users($context), $nbPage, $perPage, $url);
	?>
	<hr>

	</body>
</html>
