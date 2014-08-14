/* Javascript document
 * Requires:  -jQuery 
 * author: Scott Davies 2013
 */


$(document).ready(function() {
  $("#tblViewJobs tr > :nth-child(1), tr > :nth-child(5)").addClass("lAlign");
  $("#tblViewJobs tr > :nth-child(3), tr > :nth-child(4)").addClass("rAlign");
  
  
  $(".applicantId").on("click", function() {
    window.location = $SCRIPT_ROOT + "/views/create_applicant.php?id=" + $(this).attr("data-applicantid");
  });
  
  
  $(".jobId").on("click", function() {
    window.location = $SCRIPT_ROOT + "/views/create_job.php?id=" + $(this).attr("data-jid");
  });
  
  
  $("#btnSearchJobs").click(function() {
    var formData = {"start":  $.trim($("#start").val()),
                    "end": $.trim($("#end").val()),
                    "filled": $.trim($("#filled").val())
    };
    
    
    $.post($SCRIPT_ROOT + "/views/view_jobs.php", 
      formData, 
      function(data) {
        var errors = data["errors"];

      }, // End of post success function
      "json"
    ); // End of post call
    
  });     
  
  
});