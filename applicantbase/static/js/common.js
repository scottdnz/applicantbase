/* Javascript document
 * Requires:  -jQuery 
 * author: Scott Davies 2011
 */


function swapExpanderIcons($curElem) {
  /* Swaps the icon next to an expandable/collapsible area, to the reverse
   * value of whatever is already present. */
  var $expanderImg = $curElem.children("img");
  if ($expanderImg.hasClass("btnExpander")) {
    $expanderImg.attr("src", "../static/img/btn_collapse.png");
    $expanderImg.removeClass("btnExpander").addClass("btnCollapser");
  }
  else {
    $expanderImg.attr("src", "../static/img/btn_expand.png");
    $expanderImg.removeClass("btnCollapser").addClass("btnExpander");
  }
}


$(document).ready(function() {
  
  $("#mainMenu").find("> li").hover(function() {
    $(this).find("ul")
    .removeClass("noJS")
    .stop(true, true).slideToggle("fast");
  });
  
  
  $(".hdr").click(function() {
    var $curLink = $(this).children("a").attr("href");
    window.open($curLink, "_self");
  }).hover(
    function() {
      $(this).css("cursor", "pointer");
    },
    function() {
      $(this).css("cursor", "default");
    }
  );
  
  
  $(".resetBtn").live("click", function() {
    // Clear any rich text boxes.
    var $ed = $(".uEditorIframe").contents().find("body");
    $ed.html("");
  });
  
});