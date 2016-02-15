// Hide description and feeback by default for more visibility
$('#descriptionOptional').css({"display" : "none"});
$('#feedbackOptional').css({"display" : "none"});
$('#descriptionOptionalShow').css({"display" : "inline-block"});
$('#feebackOptionalShow').css({"display" : "inline-block"});
$('#descriptionOptionalHide').css({"display" : "none"});
$('#feebackOptionalHide').css({"display" : "none"});


//$("*[id$='_question_model']").attr("disabled", true);

// Display the textarea
function DisplayOptional(type) {
    if (type == 'feedback') {
        $('#feebackOptionalShow').css({"display" : "none"});
        $('#feebackOptionalHide').css({"display" : "inline-block"});
        $('#feedbackOptional').css({"display" : "inline-block"});
    }

    if (type == 'description') {
        $('#descriptionOptionalShow').css({"display" : "none"});
        $('#descriptionOptionalHide').css({"display" : "inline-block"});
        $('#descriptionOptional').css({"display" : "inline-block"});
    }
}

// Hide the textarea
function HideOptional(type) {
    if (type == 'feedback') {
        $('#feebackOptionalShow').css({"display" : "inline-block"});
        $('#feebackOptionalHide').css({"display" : "none"});
        $('#feedbackOptional').css({"display" : "none"});
    }

    if (type == 'description') {
        $('#descriptionOptionalShow').css({"display" : "inline-block"});
        $('#descriptionOptionalHide').css({"display" : "none"});
        $('#descriptionOptional').css({"display" : "none"});
    }
}
/**
 * Change the icon's link according to its status
 *
 * @param {string} idI : icon's id
 * @param {string} idDiv : div's id which appears or disappears
 */
function statusButton(idI,idDiv) {
    $('#'+idDiv).on('shown.bs.collapse', function () {
      $('#'+idI).removeClass('').addClass('fa fa-eye-slash');
    });
    $('#'+idDiv).on('hidden.bs.collapse', function () {
      $('#'+idI).removeClass('fa fa-eye-slash').addClass('');
  });
        }

// Delete the name of the category
function dropCategory() {
    var idCategory = $("*[id$='_question_category']").val(); // Id of the category to delete
    var path = $('#pathDrop').val(); // Path to the controller
    $.ajax({
        type: "POST",
        url: path,
        data: {
            idCategory: idCategory
        },
        cache: false,
        success: function (data) {
            // Remove the label from the list
            $("*[id$='_question_category'] option[value=\""+idCategory+"\"]").remove();
            displayDeleteCategory();
        }
    });
}

/**
 * Recover all value of the menu category
 */
var allCategory = $('#categoryArray').val();
var categoryArray = allCategory.split(';');

/**
 * For the view : question.html.twig
 * Action on the button edition and deleted of category
 *
 * @var {int} idCat Id of the category
 * @var {string} valueCat Category select
 * @var {string} locker  Locked category for this user
 */
$("*[id$='_question_category']").change(function () {
    var idCat = $("*[id$='_question_category']").val();
    var valueCat = $("*[id$='_question_category'] option:selected").text();
    var locker=$('#locker').val();
    displayDeleteCategory(idCat,valueCat,locker);
    displayEditCategory(idCat,valueCat,locker);
});

/**
 * Display or no the edition category button
 * @param {int} idCat Id of the category
 * @param {string} valueCat Category select
 * @param {string} locker Locked category for this user
 */
function displayEditCategory(idCat,valueCat,locker)
{
     for(var i = 0 ; i < categoryArray.length - 1 ; i++) {
        if (idCat == "") {
                $('#editCategory').css({"display" : "none"});
                 break;
             } else {
                 if(valueCat == locker )
                 {
                     $('#editCategory').css({"display" : "none"});
                      break;
                 }else
                 {
                     $('#editCategory').css({"display" : "inline-block"});
                 }

        }

    }
}

/**
 * Display or no the button edition category
 * @param {int} idCat Id of the category
 * @param {string} valueCat Category select
 * @param {string} locker Locked category for this user
 */
function displayDeleteCategory(idCat,valueCat,locker) {

    for(var i = 0 ; i < categoryArray.length - 1 ; i++) {
        var index = categoryArray[i].substring(0, categoryArray[i].indexOf('/'));
        var contain = categoryArray[i].substring(categoryArray[i].indexOf('/') + 1);

        if (idCat == index) {
            if (contain == 0) {
                $('#linkedCategory').css({"display" : "inline-block"});
                break;
            } else {
                $('#linkedCategory').css({"display" : "none"});
                 break;
            }
        } else {
            $('#linkedCategory').css({"display" : "inline-block"});
        }
         if (idCat == "") {
                $('#linkedCategory').css({"display" : "none"});
                 break;
             }
       if (valueCat == locker)
       {
           $('#linkedCategory').css({"display" : "none"});
                 break;
       }
       else{
           $('#linkedCategory').css({"display" : "inline-block"});
                 break;
       }
    }
}



// Delete button
function addDelete(tr, deleteTrans) {

    // Create the button to delete
    var delLink = $('<a title="'+deleteTrans+'" href="#" class="btn btn-danger"><i class="fa fa-close"></i></a>');

    // Add the button to the row
    tr.append(delLink);

    // When click, delete the matching row in the table
    delLink.click(function(e) {
        $(this).parent('td').parent('tr').remove();
        e.preventDefault();
        return false;
    });
}

$(document).ready(function() {
    $('#ujm_exobundle_interactionqcmtype_question_invite_ifr').height(50);
    displayOptionalFields();

    //on ne passe par ici pour l'instant
    $('#ujm_exobundle_interactionqcmtype_question_invite_ifr').on('load', function() {
        placeholderTinyMCE();
    });

});

function placeholderTinyMCE(){
//    $('#mce-tinymce mce-container mce-panel').sortable({
//
//    });

    $('#ujm_exobundle_interactionqcmtype_question_invite_ifr').contents().find("br").css( "background-color", "#BADA55" );
}

/**
 * Enters edit advance
 *
 * @param {String} idTextarea
 * @param {String}  btnEdition the id button edition
 * @param {Event} e
 * @returns {Boolean}
 */
function advancedEdition(idTextarea, btnEdition, e) {
    // If the navigator is chrome
    var userNavigator = navigator.userAgent;
    var positionText = userNavigator.indexOf('Chrome');

    if (positionText !== -1) {
        //the edition that after running action
        $('body').append($('<input type="hidden" id="chrome"> '));
        $('#chrome').remove();
    }

    var textarea = idTextarea === 'question_description' ?
        $("*[id$='" + idTextarea + "']") :
        $('#' + idTextarea);

    textarea.addClass('claroline-tiny-mce hide');
    textarea.data('theme', 'advanced');

    $('#' + btnEdition).remove();
    e.preventDefault();

    return false;
}

/**
 * Layout of the edition
 */
function displayOptionalFields(){
  //Value select the category
  var idCatSelect=$("*[id$='_question_category']").val();
  //Value of the description
  var valDescription =$("*[id$='_question_description']").text();

  //Value of feelback
  var valFeedback = $("*[id$='_question_feedBack']").text();

  //Test category
  if(idCatSelect !== "" )
  {
      $("#categoryDiv").collapse('show');
  }
  //Test description
  if(valDescription !== "")
  {
      $("#descriptionDiv").collapse('show');
      //Enables advanced edition
      if(valDescription.match("<.+>.+|\s<\/.+>$"))
      {
          $("*[id$='_question_description']").addClass("claroline-tiny-mce hide");
          $("*[id$=_question_description']").data("data-theme","advanced");
          $("#buttonEdition").remove();
      }
  }
  //Test feedback
  if(valFeedback !== "")
  {
      $("#collepseinteraction").collapse('show');
  }   
}

/**
 * active advanced edition
 * @returns {undefined}
 */
function textareaAdvancedEdition()
{
    //active advanced edition
    $('.classic').find('textarea').each(function() {
        //if there is at the start an open tag and a close at the end. And at the middle all caracters possible or nothing
        if($(this).val().match("<.+>.+|\s<\/.+>$")) {
            var idProposalVal = $(this).attr("id");
            $("#"+idProposalVal).addClass("claroline-tiny-mce hide");
            $("#"+idProposalVal).data("data-theme","advanced");
            $("#btnEdition_"+idProposalVal).remove();
        }
         //Show feedback answers
        if($(this).text() !== "") {
            var idspanFeedback ='span_'+$(this).attr("id");
            var idBtnFeedback = 'btn_'+$(this).attr("id"); 
            $('#'+idspanFeedback).removeAttr( 'style' );
            $('#'+idBtnFeedback).remove();
        }
    });
}
/**
 * Button feedback
 * @param {type} spanFeedback
 * @param {type} btnHiddenFeedback
 * @returns {undefined}
 */
function addTextareaFeedback(spanFeedback,btnHiddenFeedback){
     $('#'+btnHiddenFeedback).remove();
     $('#'+spanFeedback).removeAttr( 'style' );    
}
