/* Javascript document.
 * Requires:  -jQuery, 
 *            -jQuery UI for datepicker widgets, 
 *            -uEditor plugin for rich text editor.
 * author: Scott Davies 2011
 */


function addLeadingZero(intVal) {
  /* */
  var strVal = intVal.toString();
  if (intVal < 10) {
    strVal = "0" + strVal;
  }
  return strVal;
}


function setDefaultDates() {
  /* Add today's date in the format dd/mm/yyyy to the two datepicker widgets. */
  var dateFromDefault = "";
  var dateToDefault = "";
  var now = new Date();
  var year = now.getFullYear();
  var month = now.getMonth() + 1;
  var day = now.getDate();
  var nowDisplay = addLeadingZero(day) + "/" + addLeadingZero(month) + "/" + year; 
  $("#dateStarted").val(nowDisplay);
  $("#dateFilled").val(nowDisplay);
}


function initForm() {
  /* */
  setDefaultDates();
  $("#jobTitle").focus();
  $("input[name='filled'][value='0']").attr("checked", "checked");
}


$(document).ready(function() {

  
  // Initialise the uEditor plugin.
  $(".uEditor").uEditor();
  
  
  $("#dateStarted").datepicker({
    // Initialise a jQuery UI datepicker widget.
    dateFormat: "dd/mm/yy",
    changeMonth: true
  });
  
  
  $("#dateFilled").datepicker({
    // Initialise a jQuery UI datepicker widget.
    dateFormat: "dd/mm/yy",
    changeMonth: true
  });
  
  
  var imgHtml = "<img src = '../static/img/btn_collapse.png' alt = 'expander' ";
  imgHtml += "class = 'btnCollapser' />";
  $("#descriptionHdr").prepend(imgHtml).hover(
    /* When a heading is hovered over and it has results, make it 
     * obvious that it is also clickable. */
    function() {
      $(this).css("cursor", "pointer");
    },
    function() {
      $(this).css("cursor", "default");
    }
  ).click(function(e) {
    /* A header line for results has been clicked on. If the area under the 
     * header has been hidden, show it. If it is currently showing, hide it. */ 
    e.preventDefault();
    swapExpanderIcons($(this));
    $results = $(this).siblings(".resultArea");
    if ($results.hasClass("hidden")) {
      $results.removeClass("hidden");
    }
    else {
      $results.addClass("hidden");
    }
  });
  
  
  $("input[name='filled'][value='1']").click(function() {
    /* A radio button was clicked. Show a subform area and enable its inputs. 
     */
    $("#dateFilled").removeAttr("disabled");
    $("#appFilledBy").removeAttr("disabled");
    $("#dateFilledArea").removeClass("hidden");
  });
  
  
  $("input[name='filled'][value='0']").click(function() {
    /* A radio button was clicked. Hide a subform area and disable its inputs. 
     */ 
    $("#dateFilledArea").addClass("hidden");
    $("#dateFilled").attr("disabled", "disabled");
    $("#appFilledBy").attr("disabled", "disabled");
  });
  
  
  $("#jobsListSubmitBtn").click(function() {
    /* The Save button was clicked */
    $("#confirmationArea").empty();
    $("#errorMsg").empty();
    var desc = $.trim($(".uEditorIframe").contents().find("#iframeBody").html());
    // The RTE box always puts a "<br>" tag in, even if it's empty.
    if (desc == "<br>") {
      desc = "";
    }
    var formData = {"jobTitle": $("#jobTitle").val(),
                    "dateStarted": $("#dateStarted").val(),
                    "description": desc,
                    "adSource": $("#adSource").val(),
                    "filled": $("input[name='filled']:checked").val()
                  };
    /* testing */
//    var keys = [];
//    var strg = "";
//    for (key in formData) {
//      keys.push(key)
//    }
//    for (i = 0; i < keys.length; i++) {
//      strg += keys[i] + ": " + formData[keys[i]] + "\n";
//    }
//    alert(strg);
    if (formData["filled"] == "1") {
      formData["appFilledBy"] = $("#appFilledBy").val();
      formData["dateFilled"] = $("#dateFilled").val();
    }
    
    $.post("../views/create_job.php", 
        formData, 
        function(data) {
          var errors = data["errors"];
          var conf = data["confirmation"];
          // Testing.
//          var testArr = data["test_arr"];
//          for (i = 0; i < testArr.length; i++) {
//            $("#testArea").append("<li>" + testArr[i] + "</li>");
//          }
          if (errors.length > 0) {
            for (i = 0; i < errors.length; i++) {
              $("#errorMsg").append("<li>" + errors[i] + "</li>");
            }
          }
          else {
            var $confMsgArr = new Array("<ul>");
            for (i = 0; i < conf.length; i++) {
              $confMsgArr.push("<li>" + conf[i] + "</li>");
            }
            $confMsgArr.push("</ul><br />");
            $("#confirmationArea").append($confMsgArr.join(""));
            $(".uEditorIframe").contents().find("#iframeBody").html("");
            $("#jobTitle").val("");
            $("#adSource").val("");
            $("input[name='filled']:checked").val();
            initForm();
          }
        }, // End of post success function
        "json"
        ); // End of post call
  });
  
  
  initForm();
  

});