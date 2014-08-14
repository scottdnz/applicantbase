<?php 
/**
 * This file creates tables with their fields in the database.
 * 
 * @author Scott Davies
 * @version 1.0
 * @package
 */


require("db_funcs.php");
require("init_db_funcs.php");


/* Creates a table. */
function create_applicant($conn) {
	if (check_table_exists($conn, "applicant") > 0) {
		return;
	}
	$sql = "create table applicant (
id int primary key auto_increment, 
first_name varchar (32), 
surname varchar (32), 
phone_home varchar (15),  
phone_work varchar (15), 
phone_mobile varchar (13), 
email_home varchar (64), 
email_work varchar (64), 
flag_future_id int, 
narrative text);";
// position_applied_for_id int
	$result_msg_arr = run_modify_query($conn, $sql);
	return $result_msg_arr;
}


// /* Creates a table. */
// function create_applicants_applications($conn) {
// 	if (check_table_exists($conn, "applicants_applications") > 0) {
// 		return;
// 	}
// 	$sql = "create table applicants_jobs (";
// 	$sql .= "id int primary key auto_increment,";
// 	$sql .= "applicant_id int,";
// 	$sql .= "application_id int );";
// 	$result_msg_arr = run_modify_query($conn, $sql);
// 	return $result_msg_arr;
// }
	
	
/* Creates a table. */
function create_job($conn) {
	if (check_table_exists($conn, "job") > 0) {
		return;
	}
	$sql = "create table job (";
	$sql .= "id int primary key auto_increment,";
	$sql .= "title varchar (64),";
	$sql .= "date_started datetime,";
	$sql .= "description text,";
	$sql .= "ad_source varchar (32),";
	$sql .= "filled bool,";
	$sql .= "applicant_filled_by_id int,";
  $sql .= "date_filled datetime);";
	$result_msg_arr = run_modify_query($conn, $sql);
	return $result_msg_arr;
}


/* Creates a table. */
function create_position_applied_for($conn) {
	if (check_table_exists($conn, "position_applied_for") > 0) {
		return;
	}
	$sql = "create table position_applied_for (";
	$sql .= "id int primary key auto_increment,";
	$sql .= "applied_for varchar (20),";
	$sql .= "valid bool,";
	$sql .= "deflt bool);";
	$result_msg_arr = run_modify_query($conn, $sql);
	return $result_msg_arr;
}
	
	
/* Creates a table. */
function create_application($conn) {
	if (check_table_exists($conn, "application") > 0) {
		return;
	}
	$sql = "create table application (";
	$sql .= "id int primary key auto_increment,";
	$sql .= "applicant_id int,";
	$sql .= "application_source_id int,";
	$sql .= "position_applied_for_id int,";
	$sql .= "job_id int,";
	$sql .= "application_date datetime,";
	$sql .= "status_shortlisting_id int,";
	$sql .= "status_screening_id int,";
	$sql .= "interview_id int,";
	$sql .= "reject_notification_sent_id int);";
	$result_msg_arr = run_modify_query($conn, $sql);
	return $result_msg_arr;
}


/* Creates a table. */
function create_application_source($conn) {
	if (check_table_exists($conn, "application_source") > 0) {
		return;
	}
	$sql = "create table application_source (";
	$sql .= "id int primary key auto_increment,";
	$sql .= "source varchar (25),";
	$sql .= "valid bool,";
	$sql .= "deflt bool);";
	$result_msg_arr = run_modify_query($conn, $sql);
	return $result_msg_arr;
}

	
/* Creates a table. */
function create_status_shortlisting($conn) {
	if (check_table_exists($conn, "status_shortlisting") > 0) {
		return;
	}
	$sql = "create table status_shortlisting (";
	$sql .= "id int primary key auto_increment,";
	$sql .= "status varchar (25),";
	$sql .= "valid bool,";
	$sql .= "deflt bool);";
	$result_msg_arr = run_modify_query($conn, $sql);
	return $result_msg_arr;
}


/* Creates a table. */
function create_status_screening($conn) {
	if (check_table_exists($conn, "status_screening") > 0) {
		return;
	}
	$sql = "create table status_screening (";
	$sql .= "id int primary key auto_increment,";
	$sql .= "status varchar (25),";
	$sql .= "valid bool,";
	$sql .= "deflt bool);";
	$result_msg_arr = run_modify_query($conn, $sql);
	return $result_msg_arr;
}


/* Creates a table. */
function create_status_interview($conn) {
	if (check_table_exists($conn, "status_interview") > 0) {
		return;
	}
	$sql = "create table status_interview (";
	$sql .= "id int primary key auto_increment,";
	$sql .= "status varchar (25),";
	$sql .= "valid bool,";
	$sql .= "deflt bool);";
	$result_msg_arr = run_modify_query($conn, $sql);
	return $result_msg_arr;
}
	
	
/* Creates a table. */
function create_reject_notification_sent($conn) {
	if (check_table_exists($conn, "reject_notification_sent") > 0) {
		return;
	}
	$sql = "create table reject_notification_sent (";
	$sql .= "id int primary key auto_increment,";
	$sql .= "sent varchar (20),";
	$sql .= "valid bool,";
	$sql .= "deflt bool);";
	$result_msg_arr = run_modify_query($conn, $sql);
	return $result_msg_arr;
}


/* Creates a table. */
function create_attached_files($conn) {
	if (check_table_exists($conn, "attached_files") > 0) {
		return;
	}
	$sql = "create table attached_files (";
	$sql .= "id int primary key auto_increment,";
	$sql .= "filename varchar (255),";
	$sql .= "when_added datetime );";
	$result_msg_arr = run_modify_query($conn, $sql);
	return $result_msg_arr;
}


/* Creates a table. */
function create_applicants_attached_files($conn) {
	if (check_table_exists($conn, "applicants_attached_files") > 0) {
		return;
	}
	$sql = "create table applicants_attached_files (";
	$sql .= "id int primary key auto_increment,";
	$sql .= "applicant_id int,";
	$sql .= "attached_files_id int );";
	$result_msg_arr = run_modify_query($conn, $sql);
	return $result_msg_arr;
}
	
	
/* Creates a table. */
function create_interview($conn) {
	if (check_table_exists($conn, "interview") > 0) {
		return;
	}
	$sql = "create table interview (";
	$sql .= "id int primary key auto_increment,";
	$sql .= "date_of datetime,";
	$sql .= "status_interview_id int,";
	$sql .= "notes text );";
	$result_msg_arr = run_modify_query($conn, $sql);
	return $result_msg_arr;
}


/* Creates a table. */
function create_flag_future($conn) {
	if (check_table_exists($conn, "flag_future") > 0) {
		return;
	}
	$sql = "create table flag_future (";
	$sql .= "id int primary key auto_increment,";
	$sql .= "flag varchar (20),";
	$sql .= "valid bool,";
	$sql .= "deflt bool);";
	$result_msg_arr = run_modify_query($conn, $sql);
	return $result_msg_arr;
}
	
	
/* Creates a table. */
function create_user($conn) {
	if (check_table_exists($conn, "user") > 0) {
		return;
	}
	$sql = "create table user (";
	$sql .= "id int primary key auto_increment,";
	$sql .= "username varchar (50),";
	$sql .= "password_enc varchar (46),";
	$sql .= "valid bool );";
	$result_msg_arr = run_modify_query($conn, $sql);
	return $result_msg_arr;
}


/* Boilerplate table create function:
function create_($conn) {
	if (check_table_exists($conn, "") > 0) {
		return;
	}
	$sql = "create table  (";
	$sql .= "id int primary key auto_increment,";
	$sql .= "";
	$result_msg_arr = run_modify_query($conn, $sql);
	return $result_msg_arr;
} */
	
	
/**
 * Creates all the tables via functions.
 * @param "database connection object" $conn
 */
function create_all_tables($conn) {
	$result_msg_arr = array();
	while (count($result_msg_arr) < 1) {
		$result_msg_arr = create_applicant($conn);
// 		$result_msg_arr = create_applicants_applications($conn);
		$result_msg_arr = create_job($conn);
		$result_msg_arr = create_position_applied_for($conn);
		$result_msg_arr = create_application($conn);
		$result_msg_arr = create_application_source($conn);
		$result_msg_arr = create_status_shortlisting($conn);
		$result_msg_arr = create_status_screening($conn);
		$result_msg_arr = create_status_interview($conn);
		$result_msg_arr = create_reject_notification_sent($conn);
		$result_msg_arr = create_applicants_attached_files($conn);
		$result_msg_arr = create_attached_files($conn);
		$result_msg_arr = create_interview($conn);
		$result_msg_arr = create_flag_future($conn);
		$result_msg_arr = create_user($conn);
		break;
	}
	if (count($result_msg_arr) > 0) {
		echo "There was a result message: \n";
		foreach ($result_msg_arr as $result_msg) {
			echo $result_msg . "\n";
		}
	}
// 		else {
// 			echo "Tables created successfully.\n";
// 		}
	return;
}
	
	
/**
 * Main flow of program.
 */
$result_arr = get_connection();
$result = $result_arr[0];
if (strlen($result) > 0) {
	// Possible error found.
	echo $result;
	exit();
}
$conn = $result_arr[1];

drop_all_tables($conn);
create_all_tables($conn);
	
?>