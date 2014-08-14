/* Javascript document.
 * Requires:  -jQuery, 
 *            -jQuery UI for datepicker widgets, 
 *            -uEditor plugin for rich text editor.
 * author: Scott Davies 2013
 */


function isOneFilled(grp, formData) {
  /* */
  var oneFilled = false;
  for (var i = 0; i < grp.length; i++) {
    if (formData[grp[i]].length > 0) {
      oneFilled = true;
      break;
    }
  }
  return oneFilled;
}


function isNotAllFilled(grp, formData) {
  /* */
  var notAllFilled = false;
  for (var i = 0; i < grp.length; i++) {
    if (formData[grp[i]].length < 1) {
      notAllFilled = true;
      break;
    }
  }
  return notAllFilled;
}


function checkShowNotificationSent(selText, boxToShow) {
  /* If the shortlistRejectSent option chosen is "Reject", show the 
   * shortlistRejectSent checkbox. */
  if (selText.search(/reject/i) > -1) {
    $(boxToShow).show();
  }
  else {
    $(boxToShow).hide();
  }
}


function finaliseApplication() {
  /* Show field(s) for ending the application, based on a value that stops 
   * further progress. */
  var stopFlag = false;
  var noFurtherVals = new Array("Reject");
  var checkVals = new Array($("#statusShortlisting option:selected").text(),
                            $("#statusScreening option:selected").text(),
                            $("#statusInterview option:selected").text()
                            );
  for (var j = 0; j < checkVals.length; j++) {
    for (var k = 0; k < noFurtherVals.length; k++) {
      if (checkVals[j].match(noFurtherVals[k])) {
        stopFlag = true;
        break;
      } 
    }
  }
  if (stopFlag) {
    $("#lineFlagFuture").removeClass("hidden");
  }
  else {
    $("#lineFlagFuture").addClass("hidden");
  }
}


function checkShowNext(selText, nextElems, restOfElems) {
  /* Check whether subsequent fields should be shown based on selected text. */
  var notShowNextFlag = false;
  var notShowNextVals = new Array("Not decided yet", "Reject");
  for (var k = 0; k < notShowNextVals.length; k++) {
    if (selText.match(notShowNextVals[k])) {
      notShowNextFlag = true;
      for (i = 0; i < nextElems.length; i++) {
        nextElems[i].addClass("hidden");  
      }
      for (i = 0; i < restOfElems.length; i++) {
        restOfElems[i].addClass("hidden");  
      }
      break;
    } 
  }
  if (! notShowNextFlag) {
    for (i = 0; i < nextElems.length; i++) {
      nextElems[i].removeClass("hidden");  
    }
  }
}


function showHideAreas(areaToShow) {
  /* */
  var areas = new Array("step1", "step2", "editNarrative", "attachedFiles");
  for (i = 0; i < areas.length; i++) {
    if (areas[i] == areaToShow) {
      $("#" + areas[i]).show();
    }
    else {
      $("#" + areas[i]).hide();
    }
  }
}


function initForm() {
  /* */
  /* If the shortlistRejectSent option chosen is "Reject", show the 
   * shortlistRejectSent checkbox. */   
  var selText = $("#shortlistRejectSent").find("option:selected").text();
  checkShowNotificationSent(selText, "#shortlistRejectSent");
  $("#firstName").focus();
  
  showHideAreas("step1"); 
  finaliseApplication();
}


function getFieldsStep1() {
  /* */
  return {"btnSaveStep1": true,
    "firstName": $.trim($("#firstName").val()),
    "surname": $.trim($("#surname").val()),
    "phoneHome": $.trim($("#phoneHome").val()),
    "phoneWork": $.trim($("#phoneWork").val()),
    "phoneMobile": $.trim($("#phoneMobile").val()),
    "emailHome": $.trim($("#emailHome").val()),
    "emailWork": $.trim($("#emailWork").val()),
    "positionApp": $.trim($("input[name='positionApp']:checked").val()), // Radios.
    "positionLink": $.trim($("#positionLink").val()),
    "applicationSrc": $.trim($("#applicationSrc").val()),
    "applicationDate": $.trim($("#applicationDate").val())
  };
}


function checkErrorsStep1(formData) {
  /* */
  var errorArr = new Array();
  // Compulsory fields
  var grp1 = new Array("firstName", "surname");
  if (isNotAllFilled(grp1, formData)) {
    errorArr.push("Fields " + grp1.join(", ") + " are compulsory");
  }
  grp1 = new Array("phoneHome", "phoneWork", "phoneMobile");
  if (! isOneFilled(grp1, formData)) {
    errorArr.push("Fields " + grp1.join(", ") + " must have at least one filled out."); 
  }
  return errorArr;
}


$(document).ready(function() {
  
  
  // Initialise the uEditor plugin.
  $(".uEditor").uEditor();  
  
  
  $(".appOption").hover(
    function() {
    $(this).css("cursor", "pointer");
    },
    function() {
      $(this).css("cursor", "default");
    }
  );
  
  
  $(".appOption").click(function() {
    var curOption = $(this).text();
    switch (curOption) {
    case "Step 1": 
      showHideAreas("step1");
      break;
    case "Step 2":
      showHideAreas("step2");
      break;
    case "Edit Narrative":
      showHideAreas("editNarrative");
      break;
    case "Attached Files":
      showHideAreas("attachedFiles"); 
    }
  });
  
  
  /* Step 1 *************************************************************** */
  
  $("#applicationDate").datepicker({
    // Initialise a jQuery UI datepicker widget.
    dateFormat: "dd/mm/yy",
    changeMonth: true
  });
  
  
  $("input[name='positionApp']").change(function() {
    /* A radio button was clicked. Display or hide the positionLink line 
     * based on the button value. */
    var radioId = $(this).attr("id");
    var optText =$.trim($("label[for='" + radioId + "']").text());
    if (optText == "Yes") {
      $("#linePositionLink").show();
    }
    else {
      $("#linePositionLink").hide();
    }
  });
  
  
  $("#btnSaveStep1").click(function() {
    /* Save button clicked. If no errors, post fields data for storing in db. */
    $("#errorMsg").empty();
    $("#confArea").empty();
    var formData = getFieldsStep1();
    // Check for fields not filled.
//    var errorArr = checkErrorsStep1(formData); 
//    if (errorArr.length > 0) {
//      $("#errorsArea").empty();
//      for (var i = 0; i < errorArr.length; i++) {
//        $("#errorsArea").append(errorArr[i] + "<br />");
//      }
//    }
//    else {
      $.post("../views/create_applicant_process.php", 
        formData, 
        function(data) {
          var errors = data["errors"];
          var confirmation = data["confirmation"];
          if (errors.length > 0) {
            var errorStrg = "";
            for (i = 0; i < errors.length; i++) {
              errorStrg += "<li>" + errors[i] + "</li>";
            }
            $("#errorMsg").append(errorStrg);
          }
          if (confirmation.length > 0) {
            $("#confArea").append(confirmation[0] + "<br />");
          }
        }, // End of post success function
        "json"
      ); // End of post call
//    }
  });

  
  /* Step 2 *************************************************************** */
  
  $("#statusShortlisting").change(function() {
    /* Show a checkbox if a particular option is selected. */
    var nextElems = new Array($("#lineScreening"));
    var restOfElems = new Array($("#lineIntDate"), $("#lineAfterInt"), 
        $("#lineIntNotes"), $("#interviewRejectSent"), $("#screeningRejectSent"));
    var selText = $(this).find("option:selected").text();
    checkShowNotificationSent(selText, "#shortlistRejectSent");
    checkShowNext(selText, nextElems, restOfElems);
    finaliseApplication();
  });
  
  
  $("#statusScreening").change(function() {
    /* Show a checkbox if a particular option is selected. */
    var nextElems = new Array($("#lineIntDate"), $("#lineAfterInt"), $("#lineIntNotes"));
    var selText = $(this).find("option:selected").text();
    checkShowNotificationSent(selText, "#screeningRejectSent");
    checkShowNext(selText, nextElems, Array());
    finaliseApplication();
  });
  
  
  $("#interviewDate").datepicker({
    // Initialise a jQuery UI datepicker widget.
    dateFormat: "dd/mm/yy",
    changeMonth: true
  });

  
  $("#statusInterview").change(function() {
    /* Show a checkbox if a particular option is selected. */
    var selText = $(this).find("option:selected").text();
    checkShowNotificationSent(selText, "#interviewRejectSent");
    finaliseApplication();
  });
  
  
  $("#btnSaveStep2").click(function() {
    /* */
    
    $.post("../views/create_applicant_process.php", 
        formData, 
        function(data) {
          var errors = data["errors"];
          var confirmation = data["confirmation"];
//          if (errors.length > 0) {
//            var errorStrg = "";
//            for (i = 0; i < errors.length; i++) {
//              errorStrg += "<li>" + errors[i] + "</li>";
//            }
//            $("#errorMsg").append(errorStrg);
//          }
//          if (confirmation.length > 0) {
//            $("#confArea").append(confirmation[0] + "<br />");
//          }
        }, // End of post success function
        "json"
      ); // End of post call
  });
  
  
  /* Step 3 - Narrative **************************************************** */
  
  $("#btnSaveNarrative").click(function() {
    /* */
    
  });
  
  
  /* Step 4 - Attached files *********************************************** */
  
  $("#attachFile").change(function() {
    // Store form fields in a file object
    var formData = new Object();
    formData["firstName"] = $("#firstName").val();
    formData["surName"] = $("#surname").val();
    //formData["attFile"] = $("#attachFile").val();
    
    /* Post the data to a PHP script on server. Syntax:
     * jQuery.post( url, [data,] [success(data, textStatus, jqXHR),] [dataType] ) */
    $.post("../views/process_upload_file.php", 
      formData, 
      function(data) {
        var id = data["id"];
        
        var errors = data["errors"];
        if (errors.length < 1) {
          resetForm($user, $email, $deptId, $priorityId, $fullName, $subject, $msg);
          $("#phoneTicketForm").hide();
        }
        var resultMsg = getResultMsg(id, mask, errors);
        $("#submitResultArea").empty().append(resultMsg).show();
        
      }, // End of post success function
      "json"
      ); // End of post call
  });
  
  
  $("#crApplicantSaveBtn").click(function() {
    var data = {
                "applicationDate": $("#applicationDate").val(), 
                "statusShortlisting": $("#statusShortlisting").val(),
                "slRejectSent": $("#shortlistRejectSent").val(),
                "statusScreening": $("#statusScreening").val(),
                "scrRejectSent": $("#screeningRejectSent").val(),
                "interviewDate": $("#interviewDate").val(),
                "statusInterview": $("#statusInterview").val(),
                "intRejectSent": $("#interviewRejectSent").val()
                //"notes"
                //"flagFuture"
                //"jobsListSubmitBtn"
                //"attachFile"
                //"viewNarrative"
    };             //narrativeArea
  
  // Fields to pull out.
//"firstName"
//"surname"
//"phoneHome"
//"phoneWork"
//"phoneMobile"
//"emailHome"
//"emailWork"
//"positionApp"
//"applicationSrc"
//"applicationDate"
//"statusShortlisting"
//"slRejectSent"
//"statusScreening"
//"scrRejectSent"
//"interviewDate"
//"statusInterview"
//"intRejectSent"
//"notes"
//"flagFuture"
//"jobsListSubmitBtn"
//"attachFile"
//"viewNarrative"
//narrativeArea *nasty RTE with JS
  });
  
  
  initForm();
  

  
});