<?php
  /**
   * This file contains functions for use with the view applicants web page. 
   * 
   * @author Scott Davies
   * @version 1.0
   * @package lib_view applicants
   */


function get_applicants($conn) {
  $errors = "";
  $results = array();
  $sql = "select applicant.id as apct_id, applicant.first_name, applicant.surname,  
applicant.flag_future_id, application.id as apn_id,  
application.position_applied_for_id, application.job_id, job.title 
from applicant 
right join application on  
application.applicant_id = applicant.id 
left join job on 
job.id = application.job_id;";
  $res = run_query($conn, $sql);
  //$res_type=MYSQLI_NUM
  if (count($res[0]) > 0) {
    foreach ($res[0] as $err) {
      $errors .= $err;
    }
  }
  else {
    $results = $res[1];
//     for ($i = 0; $i < count($results); $i++) {
      
//     }
  }  
  return array($errors, $results);   
}

/* 
 * select id, first_name, surname, flag_future_id from applicant;
 * select id, title from job;
 * select id, applicant_id, position_applied_for_id, job_id from application;
 * 
 * 
 * select applicant.id, applicant.first_name, applicant.surname, applicant.flag_future_id, 
 * application.id, application.applicant_id, application.position_applied_for_id, application.job_id 
 * from applicant 
 * left join application on 
 * application.applicant_id = applicant.id;
 *
 * Good!:
 * 
 * select applicant.id, applicant.first_name, applicant.surname, applicant.flag_future_id, 
application.id, application.applicant_id, application.position_applied_for_id, application.job_id, job.title 
from applicant 
left join application on 
application.applicant_id = applicant.id
left join job on 
job.id = application.job_id;

Best!
select applicant.id, applicant.first_name, applicant.surname, applicant.flag_future_id, 
application.id, application.applicant_id, application.position_applied_for_id, application.job_id, job.title 
from applicant 
right join application on 
application.applicant_id = applicant.id
left join job on 
job.id = application.job_id;
 */

?>