// To import questions
var questionId = [];
var pos = 0;

// To know if sorting is up or down
var clickC = clickTi = clickTy = clickI = clickL = clickT = clickU = clickN = clickS = clickM
    = clickE = clickSps = clickSn = clickSp = clickDl = clickTl = clickQl = clickCl = clickPl = clickRl = 'no';

// Arrows to show the directions of the sorting
var upC, upTi, upTy, upI, upL, upT, upU, upN, upS, upE, upSps, upSn, upSp, upDl, upTl, upQl, upCl, upPl, upRl, upM,
    downC, downTi, downTy, downI, downL, downT, downU, downN, downS, downE, downSps, downSn, downSp,
    downDl, downTl, downQl, downCl, downPl, downRl, downM;

// Sort questions by selected column (type)
function SortQuestions(type, array) {
    selectArrows(array);
    hideArrows(array);
    switchType(type, array);
}

// Select the matching arrows of the right array (e.g. if two arrays are on one page)
function selectArrows(array) {
    if (array == 'QuestionArray' || array == 'QuestionArrayMy') {
        upC = $('#upC');
        upTi = $('#upTi');
        upTy = $('#upTy');
        upI =  $('#upI');
        downC = $('#downC');
        downTi = $('#downTi');
        downTy = $('#downTy');
        downI = $('#downI');
    } else if (array == 'QuestionArrayShared') {
        upC = $('#upCs');
        upTi = $('#upTis');
        upTy = $('#upTys');
        upI =  $('#upIs');
        downC = $('#downCs');
        downTi = $('#downTis');
        downTy = $('#downTys');
        downI = $('#downIs');
    }

    if (array == 'table') {
        upL = $('#upL');
        downL = $('#downL');
        upT = $('#upT');
        downT = $('#downT');
    }

    if (array == 'UserArray') {
        upU = $('#upU');
        upN = $('#upN');
        upS = $('#upS');
        upE = $('#upE');
        upM = $('#upM');
        downU = $('#downU');
        downN = $('#downN');
        downS = $('#downS');
        downE = $('#downE');
        downM = $('#downM');
    }

    if (array == 'user-table') {
        upSps = $('#upSps');
        upSn = $('#upSn');
        upSp = $('#upSp');
        downSps = $('#downSps');
        downSn = $('#downSn');
        downSp = $('#downSp');
    }

    if (array == 'linkDocArray') {
        upDl = $('#upDl');
        upTl = $('#upTl');
        upQl = $('#upQl');
        upCl = $('#upCl');
        upPl = $('#upPl');
        upRl = $('#upRl');
        downDl = $('#downDl');
        downTl = $('#downTl');
        downQl = $('#downQl');
        downCl = $('#downCl');
        downPl = $('#downPl');
        downRl = $('#downRl');
    }
}

// Hide all arrows
function hideArrows(array) {
    if (array == 'table') {
        upL.css({"display" : "none"});
        downL.css({"display" : "none"});
        upT.css({"display" : "none"});
        downT.css({"display" : "none"});
    } else if (array == 'UserArray') {
        upU.css({"display" : "none"});
        upN.css({"display" : "none"});
        upS.css({"display" : "none"});
        upE.css({"display" : "none"});
        upM.css({"display" : "none"});
        downU.css({"display" : "none"});
        downN.css({"display" : "none"});
        downS.css({"display" : "none"});
        downE.css({"display" : "none"});
        downM.css({"display" : "none"});
    } else if (array == 'user-table') {
        upSps.css({"display" : "none"});
        upSn.css({"display" : "none"});
        upSp.css({"display" : "none"});
        downSps.css({"display" : "none"});
        downSn.css({"display" : "none"});
        downSp.css({"display" : "none"});
    } else if (array == 'linkDocArray') {
        upDl.css({"display" : "none"});
        upTl.css({"display" : "none"});
        upQl.css({"display" : "none"});
        upCl.css({"display" : "none"});
        upPl.css({"display" : "none"});
        upRl.css({"display" : "none"});
        downDl.css({"display" : "none"});
        downTl.css({"display" : "none"});
        downQl.css({"display" : "none"});
        downCl.css({"display" : "none"});
        downPl.css({"display" : "none"});
        downRl.css({"display" : "none"});
    } else {
        upC.css({"display" : "none"});
        upTi.css({"display" : "none"});
        upTy.css({"display" : "none"});
        upI.css({"display" : "none"});
        downC.css({"display" : "none"});
        downTi.css({"display" : "none"});
        downTy.css({"display" : "none"});
        downI.css({"display" : "none"});
    }
}

// Depend on which column is clicked, sort in the good direction and display the matching arrow
function switchType(type, array) {
    switch (type) {
        case 'Category':
            if (clickC == 'no') {
                sortTable(array, 0, ASC, type);
                downC.css({"display" : "inline-block"});
                clickC = 'yes';
            } else {
                sortTable(array, 0, DESC, type);
                upC.css({"display" : "inline-block"});
                clickC = 'no';
            }
            break;
        case 'Title':
            if (clickTi == 'no') {
                sortTable(array, 1, ASC, type);
                downTi.css({"display" : "inline-block"});
                clickTi = 'yes';
            } else {
                sortTable(array, 1, DESC, type);
                upTi.css({"display" : "inline-block"});
                clickTi = 'no';
            }

            break;
        case 'Type':
            if (clickTy == 'no') {
                sortTable(array, 2, ASC, type);
                downTy.css({"display" : "inline-block"});
                clickTy = 'yes';
            } else {
                sortTable(array, 2, DESC, type);
                upTy.css({"display" : "inline-block"});
                clickTy = 'no';
            }
            break;
        case 'Invite':
            if (clickI == 'no') {
                sortTable(array, 3, ASC, type);
                downI.css({"display" : "inline-block"});
                clickI = 'yes';
            } else {
                sortTable(array, 3, DESC, type);
                upI.css({"display" : "inline-block"});
                clickI = 'no';
            }
            break;

        case 'Label':
            if (clickL == 'no') {
                sortTable(array, 2, ASC, type);
                downL.css({"display" : "inline-block"});
                clickL = 'yes';
            } else {
                sortTable(array, 2, DESC, type);
                upL.css({"display" : "inline-block"});
                clickL = 'no';
            }
            break;
        case 'kind':
            if (clickT == 'no') {
                sortTable(array, 0, ASC, type);
                downT.css({"display" : "inline-block"});
                clickT = 'yes';
            } else {
                sortTable(array, 0, DESC, type);
                upT.css({"display" : "inline-block"});
                clickT = 'no';
            }
            break;

        case 'user':
            if (clickU == 'no') {
                sortTable(array, 0, ASC, type);
                downU.css({"display" : "inline-block"});
                clickU = 'yes';
            } else {
                sortTable(array, 0, DESC, type);
                upU.css({"display" : "inline-block"});
                clickU = 'no';
            }
            break;
        case 'numPaper':
            if (clickN == 'no') {
                sortTable(array, 1, NUMA, type);
                downN.css({"display" : "inline-block"});
                clickN = 'yes';
            } else {
                sortTable(array, 1, NUMD, type);
                upN.css({"display" : "inline-block"});
                clickN = 'no';
            }
            break;
        case 'startDate':
            if (clickS == 'no') {
                sortTable(array, 2, DATEA, type);
                downS.css({"display" : "inline-block"});
                clickS = 'yes';
            } else {
                sortTable(array, 2, DATED, type);
                upS.css({"display" : "inline-block"});
                clickS = 'no';
            }
            break;
        case 'endDate':
            if (clickE == 'no') {
                sortTable(array, 3, DATEA, type);
                downE.css({"display" : "inline-block"});
                clickE = 'yes';
            } else {
                sortTable(array, 3, DATED, type);
                upE.css({"display" : "inline-block"});
                clickE = 'no';
            }
            break;

            case 'pseudo':
            if (clickSps == 'no') {
                sortTable(array, 0, ASC, type);
                downSps.css({"display" : "inline-block"});
                clickSps = 'yes';
            } else {
                sortTable(array, 0, DESC, type);
                upSps.css({"display" : "inline-block"});
                clickSps = 'no';
            }
            break;
        case 'name':
            if (clickSn == 'no') {
                sortTable(array, 1, ASC, type);
                downSn.css({"display" : "inline-block"});
                clickSn = 'yes';
            } else {
                sortTable(array, 1, DESC, type);
                upSn.css({"display" : "inline-block"});
                clickSn = 'no';
            }
            break;
        case 'fname':
            if (clickSp == 'no') {
                sortTable(array, 2, ASC, type);
                downSp.css({"display" : "inline-block"});
                clickSp = 'yes';
            } else {
                sortTable(array, 2, DESC, type);
                upSp.css({"display" : "inline-block"});
                clickSp = 'no';
            }
            break;

        case 'dateL':
            if (clickDl == 'no') {
                sortTable(array, 0, DATEA, type);
                downDl.css({"display" : "inline-block"});
                clickDl = 'yes';
            } else {
                sortTable(array, 0, DATED, type);
                upDl.css({"display" : "inline-block"});
                clickDl = 'no';
            }
            break;
        case 'titleL':
            if (clickTl == 'no') {
                sortTable(array, 1, ASC, type);
                downTl.css({"display" : "inline-block"});
                clickTl = 'yes';
            } else {
                sortTable(array, 1, DESC, type);
                upTl.css({"display" : "inline-block"});
                clickTl = 'no';
            }
            break;
        case 'QuestionL':
            if (clickQl == 'no') {
                sortTable(array, 2, ASC, type);
                downQl.css({"display" : "inline-block"});
                clickQl = 'yes';
            } else {
                sortTable(array, 2, DESC, type);
                upQl.css({"display" : "inline-block"});
                clickQl = 'no';
            }
            break;
        case 'CategoryL':
            if (clickCl == 'no') {
                sortTable(array, 3, ASC, type);
                downCl.css({"display" : "inline-block"});
                clickCl = 'yes';
            } else {
                sortTable(array, 3, DESC, type);
                upCl.css({"display" : "inline-block"});
                clickCl = 'no';
            }
            break;
        case 'paperL':
            if (clickPl == 'no') {
                sortTable(array, 4, ASC, type);
                downPl.css({"display" : "inline-block"});
                clickPl = 'yes';
            } else {
                sortTable(array, 4, DESC, type);
                upPl.css({"display" : "inline-block"});
                clickPl = 'no';
            }
            break;
        case 'responseL':
            if (clickRl == 'no') {
                sortTable(array, 5, ASC, type);
                downRl.css({"display" : "inline-block"});
                clickRl = 'yes';
            } else {
                sortTable(array, 5, DESC, type);
                upRl.css({"display" : "inline-block"});
                clickRl = 'no';
            }
            break;
        case 'mark':
            if (clickM == 'no') {
                sortTable(array, 6, SCOREA, type);
                downM.css({"display" : "inline-block"});
                clickM = 'yes';
            } else {
                sortTable(array, 6, SCORED, type);
                upM.css({"display" : "inline-block"});
                clickM = 'no';
            }
            break;
    }
}
// To sort decreasing
function DESC(a, b) {
    a = a[1];
    b = b[1];

    if (a > b) {
        return -1;
    }
    if (a < b) {
        return 1;
    }
    return 0;
}

// To sort increasing
function ASC(a, b) {
    a = a[1];
    b = b[1];

    if (a > b) {
        return 1;
    }
    if (a < b) {
        return -1;
    }
    return 0;
 }

// To sort increasing
function NUMA(a, b) {
    return a[1] - b[1];
}

// To sort decreasing
function NUMD(a, b) {
    return b[1] - a[1];
}

function DATE(x) {
    var datex, yx, mx, dx, hx, mix, sx;

    datex = x[1];

    dx = datex.substring(0, datex.indexOf('/'));
    mx = datex.substring(datex.indexOf('/') + 1, datex.indexOf('/') + 3);
    yx = datex.substring(datex.lastIndexOf('/') + 1, datex.indexOf('-') - 1);
    hx = datex.substring(datex.indexOf('-') + 2, datex.indexOf('h'));
    mix = datex.substring(datex.indexOf('h') + 1, datex.indexOf('m'));
    sx = datex.substring(datex.indexOf('m') + 1, datex.indexOf('s'));

    dx = new Date(yx, (parseInt(mx) - 1), dx, hx, mix, sx);

    if (dx == 'Invalid Date') {
        dx = new Date(0000, 00, 00, 00, 00, 00);
    }

    return (dx);
}

function DATEA(a, b) {
    var datea = DATE(a);
    var dateb = DATE(b);

    if (datea > dateb) {
        return 1;
    } else if (datea < dateb) {
        return -1;
    } else {
        return 0;
    }
}

function DATED(a, b) {
    var datea = DATE(a);
    var dateb = DATE(b);

    if (datea > dateb) {
        return -1;
    } else if (datea < dateb) {
        return 1;
    } else {
        return 0;
    }
}

function SCOREA(a,b) {
    scorea = a[1].substring(0, a[1].indexOf(' / '));
    scoreb = b[1].substring(0, b[1].indexOf(' / '));

    return scorea - scoreb;
}

function SCORED(a,b) {
    scorea = a[1].substring(0, a[1].indexOf(' / '));
    scoreb = b[1].substring(0, b[1].indexOf(' / '));

    return scoreb - scorea;
}

// To display the rows in the right order
function sortTable(tid, col, ord, type) {

    var sorter = new Array(); // Javascript array to do the sorting
    sorter.length = 0;
    var contenu;

    // Get the contain of each line in order to sort it
    $('#' + tid + ' tr').each(function () {
        if ($(this).find('td').eq(col).html() != null) {
            // Sort type of document
            if (type == 'kind') {
                contenu =  $(this).find('td').eq(col).html().trim();
                var iconLabel = contenu.substring(contenu.indexOf('-') + 1, contenu.indexOf('">'));
                sorter.push([$(this), iconLabel]);
            // Sort href
            } else if (type == 'Title'){
                contenu =  $(this).find('td').eq(col).html().toLowerCase();
                var link = contenu.substring(contenu.indexOf('">') + 2, contenu.indexOf('</'));
                sorter.push([$(this), link]);
            // Sort string
            } else {
                contenu =  $(this).find('td').eq(col).html().toLowerCase().trim();
                sorter.push([$(this), contenu]);
            }
        }
    });

    // Sort the rows
    sorter.sort(ord);

    var j = -1;

    // Rearrange the html array's rows accordind to the sorting
    while(sorter[++j]) {
        $('#' + tid).append(sorter[j][0]);
    }
}

// To search questions (with parameters in all the user's questions)
function searchQuestion(path, page, exoID) {

    // The text to find
    var whatToFind = $('#what2search').val();
    // What column is selected (category, type, title, contain)
    var type = $('input[type=radio][name=QuestionSearch]:checked').attr('value');
    // In which database of questions (user's or shared)
    var where = $('input[type=radio][name=WhereSearch]:checked').attr('value');

    // Send theses informations to the controller to have the matching questions and display it
    $.ajax({
        type: 'GET',
        url: path,
        data: {
            type : type,
            whatToFind : whatToFind,
            where : where,
            page: page,
            exoID: exoID
        },
        cache: false,
        success: function (data) {
            $('#resultSearch').html(data);
       }
    });
}

// If user change page after search, this function keep the parameters of the research and display it on the new page
window.onload = function () {

    // If has already done a question research
    if ($('#type').length > 0 && $('#whatToFind').length > 0 && $('#where').length > 0) {
        // What did he selected before changing page
        var type = $('#type').val();
        var whatToFind = $('#whatToFind').val();
        var where = $('#where').val();

        // Select and put the right old user's choices
        $('#what2search').val(whatToFind);

        if (type == 'Category') {
            $("input[type=radio][name=QuestionSearch][value='Category']").attr('checked', true);
        } else if (type == 'Type') {
            $("input[type=radio][name=QuestionSearch][value='Type']").attr('checked', true);
        } else if (type == 'Title') {
            $("input[type=radio][name=QuestionSearch][value='Title']").attr('checked', true);
        } else if (type == 'Contain') {
            $("input[type=radio][name=QuestionSearch][value='Contain']").attr('checked', true);
        } else if (type == 'All') {
            $("input[type=radio][name=QuestionSearch][value='All']").attr('checked', true);
        }

        if (where == 'my') {
            $("input[type=radio][name=WhereSearch][value='my']").attr('checked', true);
        } else if (where == 'shared') {
            $("input[type=radio][name=WhereSearch][value='shared']").attr('checked', true);
        } else if (where == 'all') {
            $("input[type=radio][name=WhereSearch][value='all']").attr('checked', true);
        }
    }

    // If has already done a document research, put the values of the research and display selected inline-block when changing page
    if ($('#label2Find').length > 0 && $('#whichAction').length > 0) {
        if ($('#label2Find').val() != '') {
            $('#labelToFind').val($('#label2Find').val());
            $('#searchDocuments').css({"display" : "inline-block"});
        }

        if ($('#whichAction').val() == 'sort') {
            $('#sortDocuments').css({"display" : "inline-block"});
        }
    }
};

function searchUserPaper(path, exoID) {

    var userName = $('#nameUser').val();

    // Send theses informations to the controller to have the matching questions and display it
    $.ajax({
        type: 'GET',
        url: path,
        data: {
            userName: userName,
            exoID:    exoID
        },
        cache: false,
        success: function (data) {
            $('#resultSearch').html(data);
       }
    });
}

function displayAllQuestionInSearch(pathSearch, exoID, displayAll) {
    var type = $('#type').val();
    var whatToFind = $('#whatToFind').val();
    var where = $('#where').val();
    var page = 1;

    $.ajax({
        type: 'GET',
        url: pathSearch,
        data: {
            exoID : exoID,
            type : type,
            whatToFind : whatToFind,
            where : where,
            page : page,
            displayAll : displayAll
        },
        cache: false,
        success: function (data) {
            $('#resultSearch').html(data);
        }
    });
}

function getQuestionsExo(idExo, path, pathHome) {
    var pathRight = '';
    if (idExo == -1) {
        pathRight = pathHome;
    } else {
        pathRight = path + '/' + idExo;
    }
    window.location.href = pathRight;
}

function getQuestionsExoImport(idExo, path, pathHome) {
    var pathRight = '';

    if (idExo == -1) {
        pathRight = pathHome;
    } else {
        var locationEnd = path.lastIndexOf('/');
        var pathStart = path.substr(0, locationEnd);
        var locationStart = pathStart.lastIndexOf('/');
        pathRight = pathStart.substr(0, locationStart + 1) + idExo + path.substr(locationEnd);
    }

    window.location.href = pathRight;
}


function briefSearch(path, exoID, where) {

    var userSearch = $('#briefSearch').val();

    $.ajax({
        type: 'POST',
        url: path,
        data: {
            userSearch : userSearch,
            exoID: exoID,
            where: where
        },
        cache: false,
        success: function (data) {
            $('body').html(data);
            ready();
            createValidationBox();
        }
    });
}

function briefSearchSubmit() {
    $('#formBriefSearch').submit();
}

function showInfos() {
    if ($('#infosSearch').css('display') == 'none') {
        $('#infosSearch').css({'display' : 'block'});
    } else {
        $('#infosSearch').css({'display' : 'none'});
    }
}
