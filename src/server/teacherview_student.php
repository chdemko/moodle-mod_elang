<?php
require_once("../../config.php");
require_once($CFG->libdir . '/completionlib.php');

// TO DO: verify if viewer is teacher


$id = required_param('id', PARAM_INT); 
$idStd = required_param('idStd', PARAM_INT);
$url = new moodle_url('/mod/elang/teacherview_student.php', array('id'=>$id));
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

$PAGE->set_context($context);
$PAGE->set_url($url);
$PAGE->set_heading($course->fullname);
$nom = $DB->get_record_sql('SELECT lastname, firstname FROM mdl_user WHERE id = ?', array($idStd));
$title = $elang->name.": ".$nom->lastname." ".$nom->firstname;

?>
<html<?php echo get_html_lang(); ?>>
	<head>
		<meta charset="utf-8">
		<meta http-equiv="X-UA-Compatible" content="IE=edge,chrome=1">
		<meta name="viewport" content="width=device-width, initial-scale=1.0, maximum-scale=1.0, user-scalable=no"/>
		<title><?php echo format_string($title); ?></title>
		<style>
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
			.gradeslang{
				display: inline-block;
				margin-right:25%;
				vertical-align:bottom;
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
			.backToIndex{
				background:red;
				display: inline;
				color: white;
				padding:5px 7px 5px 7px;
				border-radius:5px;
			}
			a{text-decoration:none}
		</style>
		<body>
			<?php
		echo "<h1>".$title."</h1>";
		echo "<a href='/mod/elang/teacherview.php/?id=".$id."' title='back to overall view'><div class='backToIndex'  title='back to Students view'><<</div></a><hr>";		
		// CREATION OF A SOLUTIONS ARRAY
	
		$solutions = $DB->get_records_sql("SELECT id, title, json FROM mdl_elang_cues WHERE id_elang = ? GROUP BY id", array($cm->instance));
		$obj = array();
		foreach($solutions as $key){
			$tempTab = json_decode($key->json, true);
			$obj[$key->id]=array();
			$obj[$key->id]['title'] = $key->title;
			foreach($tempTab as $i => $tempKey){		
				if($tempKey['type']=='input'){
					$obj[$key->id][$i] = $tempKey['content'];
				}
			}
			if(empty($obj[$key->id])){
				unset($obj[$key->id]);
			}
		}
		
		// CREATION OF AN ARRAY WITH STUDENTS RECORDS
		
		$essais = $DB->get_records_sql("SELECT id, id_cue, json FROM mdl_elang_users WHERE id_elang = ? AND id_user = ?", array($cm->instance, $idStd));
		$tabessais = array();
		
		foreach($essais as $key){
			$tabessais[$key->id]["id_cue"] = $key->id_cue;
			$tabessais[$key->id]["json"] = json_decode($key->json, true);
		}
		
		// DISPLAY
		$arrayCue = array();
		foreach($tabessais as $key){
				$arrayCue[$key['id_cue']] = array(0,0,0);
			foreach($key['json'] as $i=>$keyJ){
				if($keyJ['help']){
				 	$arrayCue[$key['id_cue']][2]++;
				}else{
					if($obj[$key['id_cue']][$i]==$keyJ['content']){
						$arrayCue[$key['id_cue']][1]++;
					}else{
						$arrayCue[$key['id_cue']][0]++;
					}
				}
			}

			echo "<div class='etulang'>".$obj[$key['id_cue']]['title']."</div><div class='gradeslang'><span class='errlang'>".$arrayCue[$key['id_cue']][0]."</span><span class='correclang'>".$arrayCue[$key['id_cue']][1]."</span><span class='helplang'>".$arrayCue[$key['id_cue']][2]."</span></div>";
			
		}
		echo "<hr><a href='/mod/elang/teacherview.php/?id=".$id."' title='back to overall view'<div class='backToIndex' href='#' title='back to Students view'><<</div></a>";
			?>
	</body>
</html>
