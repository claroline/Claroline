// :::::::::::::::::::::::::::::::::::::::::: Declaration variables ::::::::::::::::::::::::::::::::::::::::::::::::::::::::::::::: 
var AnswerZones; // Tab contains all informations of Coords
var canvas  = document.querySelector('#canvas'); // Where is draw the image
var context = canvas.getContext('2d'); // To draw the image
var el = document.getElementById('movable'); // To get the shape and the color of the answer zone
var imgx; // Coord x of the answer zone
var imgy; // Coord y of the answer zone
var pic = new Image(); // The question image
var pressMAJ; // If key MAJ press or not
var pressCTRL; // If key CTRL press or not
var pressTAB; // If key VERR. MAJ press or not
var sens; // Move of the mouse
var scalex = 0; // Width of the image after resize
var scaley = 0; // Height of the image after resize
var value = 0; // Size of the resizing
var x = 0; // Mouse x after move
var xPrecedent = 0; // Mouse x before move
var y = 0; // Mouse y after move
var yPrecedent = 0; // Mouse y before move

// Display alert into navigator language
if (navigator.browserLanguage) {
    var language = navigator.browserLanguage; // IE
} else {
    var language = navigator.language; // FIrefox
}

// :::::::::::::::::::::::::::::::::::::::::: Functions ::::::::::::::::::::::::::::::::::::::::::::::::::::::::::::::: 

// Display the selected picture 
function LoadPic() {

    var list = document.InterGraphForm.ujm_exobundle_interactiongraphictype_document; // The list of all the user's pictures
    var select = list.options[list.selectedIndex].innerHTML; // Label of the selected picture

    sendData(select);

    pic.onload = function () {
        canvas.width = pic.width;
        canvas.height = pic.height;

        context.clearRect(0, 0, canvas.width, canvas.height);

        context.drawImage(pic, 0, 0);

        document.getElementById('imgwidth').value = pic.width; // Pass width of the image to the controller
        document.getElementById('imgheight').value = pic.height; // Pass height of the image to the controller

        scalex = pic.width;
        scaley = pic.height;
    }
    // New picture load, initialization var :
    value = 0;
    AnswerZones = [];
    document.getElementById('coordsZone').value = 0;
}

// Get the url's picture matching to the label in the list
function sendData(select) {

    // Send the label of the picture to get the adress in order to display it
    $.ajax({
        type: "POST",
        url: "/Claroline/web/app_dev.php/exercise/question/InteractionGraphic/DisplayPic",
        data: {
            value : select
        },
        cache: false,
        success: function (data) { 
            result = data.substr((data.indexOf('>/') + 2));
            pic.src = result;
        }                   
    });     
}

// Submit form without an empty field
function Verifier() {
    
    var imgOk = false; // Image is upload
    var questionOk = false; // Question is asked
    var titleOk = false; // Question has a title
    var zoneOk = false; // Answer zones are defined
     
    // No title 
    if (document.InterGraphForm.ujm_exobundle_interactiongraphictype_interaction_question_title.value == "") { 
        if (language.indexOf('fr') > -1) {
            alert('La question n\'a pas de titre.');
            return false;
        } else {
            alert('The question has no title.');
            return false;
        }
    } else {
        titleOk = true;
    }
    
    // No question asked
    if (document.InterGraphForm.ujm_exobundle_interactiongraphictype_interaction_invite.value == "" && titleOk == true) { 
        if (language.indexOf('fr') > -1) {
            alert('Vous ne posez aucune question ...');
            return false;
        } else {
            alert('You ask for nothing ...');
            return false;
        }
    } else {
        questionOk = true;
    }
    
    // No picture load
    if (document.getElementById('imgwidth').value == 0 && titleOk == true && questionOk == true) {
        if (language.indexOf('fr') > -1) {
            alert('Vous devez télécharger une image !');
            return false;
        } else {
            alert('You must upload a picture !');
            return false;
        }
    } else {
        imgOk = true;
    }
    
    // No answer zone
    if (document.getElementById('coordsZone').value == 0 && imgOk == true && titleOk == true && questionOk == true) {
        if (language.indexOf('fr') > -1){
            alert('Vous n\'avez mis aucune zone de réponse ...');
            return false;
        } else {
            alert('There is no answer zone ...');
            return false;
        }
    } else {
        zoneOk = true;
    }
    
    // Submit if required fields not empty 
    if (imgOk == true && zoneOk == true && titleOk == true && questionOk == true) {
        document.getElementById('InterGraphForm').submit();
    }
}

// Change the shape and the color of the answer zone
function changezone() {
    
    if (document.getElementById('shape').value == "circle") {
        switch (document.getElementById('color').value) {
        case "white" :
            document.getElementById('movable').src = "/Claroline/web/bundles/ujmexo/images/graphic/circlew.png";
            break;

        case "red" :
            document.getElementById('movable').src = "/Claroline/web/bundles/ujmexo/images/graphic/circler.png";
            break;

        case "blue" :
            document.getElementById('movable').src = "/Claroline/web/bundles/ujmexo/images/graphic/circleb.png";
            break;

        case "purple" :
            document.getElementById('movable').src = "/Claroline/web/bundles/ujmexo/images/graphic/circlep.png";
            break;

        case "green" :
            document.getElementById('movable').src = "/Claroline/web/bundles/ujmexo/images/graphic/circleg.png";
            break;

        case "orange" :
            document.getElementById('movable').src = "/Claroline/web/bundles/ujmexo/images/graphic/circleo.png";
            break;

        case "yellow" :
            document.getElementById('movable').src = "/Claroline/web/bundles/ujmexo/images/graphic/circley.png";
            break;

        default :
            document.getElementById('movable').src = "/Claroline/web/bundles/ujmexo/images/graphic/circlew.png";
            break;
        }         

    } else if(document.getElementById('shape').value == "rect") {
        switch (document.getElementById('color').value) {
        case "white" :
            document.getElementById('movable').src = "/Claroline/web/bundles/ujmexo/images/graphic/rectanglew.jpg";
            break;

        case "red" :
            document.getElementById('movable').src = "/Claroline/web/bundles/ujmexo/images/graphic/rectangler.jpg";
            break;

        case "blue" :
            document.getElementById('movable').src = "/Claroline/web/bundles/ujmexo/images/graphic/rectangleb.jpg";
            break;

        case "purple" :
            document.getElementById('movable').src = "/Claroline/web/bundles/ujmexo/images/graphic/rectanglep.jpg";
            break;

        case "green" :
            document.getElementById('movable').src = "/Claroline/web/bundles/ujmexo/images/graphic/rectangleg.jpg";
            break;

        case "orange" :
            document.getElementById('movable').src = "/Claroline/web/bundles/ujmexo/images/graphic/rectangleo.jpg";
            break;

        case "yellow" :
            document.getElementById('movable').src = "/Claroline/web/bundles/ujmexo/images/graphic/rectangley.jpg";
            break;

        default :
            document.getElementById('movable').src = "/Claroline/web/bundles/ujmexo/images/graphic/rectanglew.jpg";
        }
    }
}

function  ResizeImg(sens) {

    if (sens == 'gauche') {
        value -= 27;
    }else if (sens == 'droite') {
        value += 27;
    }

    scalex = pic.width + value; // New picture width

    var ratio = pic.height / pic.width;
    scaley = scalex * ratio; // New picture height proportional to width

    if (scalex > 27 && scaley > 27) { // Not resize too small or negativ

        context.clearRect(0, 0, canvas.width, canvas.height);
        
        canvas.width = scalex;
        canvas.height = scaley;

        context.drawImage(pic, 0, 0, scalex, scaley);

        document.getElementById('imgwidth').value = scalex;
        document.getElementById('imgheight').value = scaley;

        // Réinitialization of answer zones
        AnswerZones = [];
        document.getElementById('coordsZone').value = 0;
    }
}

// :::::::::::::::::::::::::::::::::::::::::: EventListener ::::::::::::::::::::::::::::::::::::::::::::::::::::::::::::::: 

document.addEventListener('keydown', function (e) {
    
    if (e.keyCode == 16) { // Touch MAJ down
        pressMAJ = true;
        document.body.style.cursor='nw-resize';
    }
    
    if (e.keyCode == 17) { // Touch CTRL down
        pressCTRL = true;
        document.body.style.cursor='move';
    }
    
    if (e.keyCode == 20) { // Touch VERR. MAJ down
        pressTAB = true;
        //document.body.style.cursor='suppr';
    }
}, false);

document.addEventListener('keyup', function (e) {
    
    if (e.keyCode == 16) { // Touch MAJ up
        pressMAJ = false;
        document.body.style.cursor='default';
    }
    
    if (e.keyCode == 17) { // Touch CTRL up
        pressCTRL = false;
        document.body.style.cursor='default';
    }
    
    if (e.keyCode == 20) { // Touch VERR. MAJ up
        pressTAB = false;
        //document.body.style.cursor='default';
    }
}, false);

document.addEventListener('mousemove', function (event) { // To resize the selected picture
    
    if (pressMAJ == true) {
        xPrecedent = x;
        yPrecedent = y;

        if (event.x != undefined && event.y != undefined) { // IE
            x = event.layerX;
            y = event.layerY;
        } else { // Firefox
            x = event.clientX + document.body.scrollLeft + document.documentElement.scrollLeft;
            y = event.clientY + document.body.scrollTop + document.documentElement.scrollTop;
        }

        x -= canvas.offsetLeft; // MouseX position
        y -= canvas.offsetTop;  // MouseY position

        if (x < xPrecedent) { // Gauche
            sens = 'gauche';
        } else if(x > xPrecedent) { // Droite
            sens = 'droite';
        }

        ResizeImg(sens);

        pressMAJ = false; 
    }
});

document.addEventListener('click', function (e) { // To add/delete answer zones

    if (pressCTRL == true) {
        
        // Position de la souris dans la fenetre :
        if (e.x != undefined && e.y != undefined) { // IE
            mousex = e.layerX;
            mousey = e.layerY;
        } else { // Firefox
            mousex = e.clientX + document.body.scrollLeft + document.documentElement.scrollLeft;
            mousey = e.clientY + document.body.scrollTop + document.documentElement.scrollTop;
        }
        
        var t1 = mousex - 10; // Position x of the mouse
        var t2 = mousey - 10; // Position y of the mouse
        var t3 = canvas.offsetLeft + scalex; // Width of the image
        var t4 = canvas.offsetTop + scaley; // Height of the image

        if ((t1) > (t3) || (t1) < (canvas.offsetLeft - 10) || (t2) > (t4) || (t2) < (canvas.offsetTop - 10)) { // Out the picture
            if (language.indexOf('fr') > -1) {
                alert('Vous devez mettre la zone de reponse DANS l\'image ...');
            } else {
                alert('You must put the answer zone INSIDE the picture ...');
            }
            document.body.style.cursor = 'default';
        } else {
            var img = new Image(); // A new answer zone
            img.src = el.src;

            imgx = mousex - canvas.offsetLeft;
            imgy = mousey - canvas.offsetTop;

            context.drawImage(img, imgx - 10, imgy - 10);
            
            // Add the new answer zone informations to the tab in order to send it to the controller 
            var val = img.src + ';' + imgx + "_" + imgy + "-" + document.getElementById('points').value;
            AnswerZones.push(val);
        }
        pressCTRL = false;

        // Send the answer zones to the controller
        document.getElementById('coordsZone').value = AnswerZones;
    }
    
    if (pressTAB == true) {
        
        // Position de la souris dans la fenetre :
        if (e.x != undefined && e.y != undefined ){ // IE
            x = e.layerX;
            y = e.layerY;
        } else { // Firefox
            x = e.clientX + document.body.scrollLeft + document.documentElement.scrollLeft;
            y = e.clientY + document.body.scrollTop + document.documentElement.scrollTop;
        }
        
        // Position de la souris dans l'image :
        x -= canvas.offsetLeft;
        y -= canvas.offsetTop;

        // Suppression de l'element selectionné
        for (var i = 0, c = AnswerZones.length; i < c; i++) {
            
            t = AnswerZones[i];
            ts = t.substr(0, t.indexOf(';'));
            tx = t.substring(t.indexOf(';') + 1, t.indexOf('_'));
            ty = t.substring(t.indexOf('_') + 1, t.indexOf('-'));
       
            tx1 = tx - 10;
            tx2 = parseInt(tx) + 10;
            ty1 = ty - 10;
            ty2 = parseInt(ty) + 10;
            

            if (x > tx1 && x < tx2 && y > ty1 && y < ty2) {
                AnswerZones.splice(i,1); 
                if (i == 0 && AnswerZones.length < 1) {
                    document.getElementById('coordsZone').value = 0;
                }
                break;
            }
        } 
 
        document.getElementById('coordsZone').value = AnswerZones;
        
        context.clearRect(0, 0, canvas.width, canvas.height);
        
        // Réaffichage de l'image
        context.drawImage(pic, 0, 0, scalex, scaley);
          
        // Réaffichage des zones de reponses non supprimées
        for (var z = 0, l = AnswerZones.length; z < l; z++) {
                 
            t = AnswerZones[z];
            ts = t.substr(0, t.indexOf(';'));
            tx = t.substring(t.indexOf(';') + 1, t.indexOf('_'));
            ty = t.substring(t.indexOf('_') + 1, t.indexOf('-'));

            tx1 = tx - 10;
            tx2 = parseInt(tx) + 10;
            ty1 = ty - 10;
            ty2 = parseInt(ty) + 10;

            var zone = new Image();
            zone.src = ts;
            context.drawImage(zone, tx - 10, ty - 10);
        }      
        pressTAB = false;
    }  
}, false);