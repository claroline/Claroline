// To know if sorting is up or down
var clickC = clickTi = clickTy = clickI = clickL = clickT = clickU = clickN = clickS
    = clickE = clickSps = clickSn = clickSp = clickDl = clickTl = clickQl = clickCl = clickPl = clickRl = 'no';

// Arrows to show the directions of the sorting
var upC, upTi, upTy, upI, upL, upT, upU, upN, upS, upE, upSps, upSn, upSp, upDl, upTl, upQl, upCl, upPl, upRl,
    downC, downTi, downTy, downI, downL, downT, downU, downN, downS, downE, downSps, downSn, downSp,
    downDl, downTl, downQl, downCl, downPl, downRl;

// Sort questions by selected column (type)
function SortQuestions(type, array) {
    selectArrows(array);
    hideArrows(array);
    switchType(type, array);
}

function selectArrows(array) {
    // Select the matching arrows of the right array (e.g. if two arrays in one page
    if (array == 'QuestionArray' || array == 'QuestionArrayMy'){
        upC = document.getElementById('upC');
        upTi = document.getElementById('upTi');
        upTy = document.getElementById('upTy');
        upI =  document.getElementById('upI');
        downC = document.getElementById('downC');
        downTi = document.getElementById('downTi');
        downTy = document.getElementById('downTy');
        downI = document.getElementById('downI');
    } else if (array == 'QuestionArrayShared') {
        upC = document.getElementById('upCs');
        upTi = document.getElementById('upTis');
        upTy = document.getElementById('upTys');
        upI =  document.getElementById('upIs');
        downC = document.getElementById('downCs');
        downTi = document.getElementById('downTis');
        downTy = document.getElementById('downTys');
        downI = document.getElementById('downIs');
    }

    if (array == 'table') {
        upL = document.getElementById('upL');
        downL = document.getElementById('downL');
        upT = document.getElementById('upT');
        downT = document.getElementById('downT');
    }

    if (array == 'UserArray') {
       upU = document.getElementById('upU');
       upN = document.getElementById('upN');
       upS = document.getElementById('upS');
       upE = document.getElementById('upE');
       downU = document.getElementById('downU');
       downN = document.getElementById('downN');
       downS = document.getElementById('downS');
       downE = document.getElementById('downE');
    }

    if (array == 'user-table') {
        upSps = document.getElementById('upSps');
        upSn = document.getElementById('upSn');
        upSp = document.getElementById('upSp');
        downSps = document.getElementById('downSps');
        downSn = document.getElementById('downSn');
        downSp = document.getElementById('downSp');
    }

    if (array == 'linkDocArray') {
        upDl = document.getElementById('upDl');
        upTl = document.getElementById('upTl');
        upQl = document.getElementById('upQl');
        upCl = document.getElementById('upCl');
        upPl = document.getElementById('upPl');
        upRl = document.getElementById('upRl');
        downDl = document.getElementById('downDl');
        downTl = document.getElementById('downTl');
        downQl = document.getElementById('downQl');
        downCl = document.getElementById('downCl');
        downPl = document.getElementById('downPl');
        downRl = document.getElementById('downRl');
    }
}

function hideArrows(array) {
    // Hide all arrows
    if (array == 'table') {
        upL.style.display = 'none';
        downL.style.display = 'none';
        upT.style.display = 'none';
        downT.style.display = 'none';
    } else if (array == 'UserArray') {
        upU.style.display = 'none';
        upN.style.display = 'none';
        upS.style.display = 'none';
        upE.style.display = 'none';
        downU.style.display = 'none';
        downN.style.display = 'none';
        downS.style.display = 'none';
        downE.style.display = 'none';
    } else if (array == 'user-table') {
        upSps.style.display = 'none';
        upSn.style.display = 'none';
        upSp.style.display = 'none';
        downSps.style.display = 'none';
        downSn.style.display = 'none';
        downSp.style.display = 'none';
    } else if (array == 'linkDocArray') {
        upDl.style.display = 'none';
        upTl.style.display = 'none';
        upQl.style.display = 'none';
        upCl.style.display = 'none';
        upPl.style.display = 'none';
        upRl.style.display = 'none';
        downDl.style.display = 'none';
        downTl.style.display = 'none';
        downQl.style.display = 'none';
        downCl.style.display = 'none';
        downPl.style.display = 'none';
        downRl.style.display = 'none';
    } else {
        upC.style.display = 'none';
        upTi.style.display = 'none';
        upTy.style.display = 'none';
        upI.style.display = 'none';
        downC.style.display = 'none';
        downTi.style.display = 'none';
        downTy.style.display = 'none';
        downI.style.display = 'none';
    }
}

function switchType(type, array) {
    // Depend on which column were clicked, sort in the good direction and display the matching arrow
    switch (type) {
        case 'Category':
            if (clickC == 'no') {
                sortTable(array, 0, ASC, type);
                downC.style.display = 'block';
                clickC = 'yes';
            } else {
                sortTable(array, 0, DESC, type);
                upC.style.display = 'block';
                clickC = 'no';
            }
            break;
        case 'Title':
            if (clickTi == 'no') {
                sortTable(array, 1, ASC, type);
                downTi.style.display = 'block';
                clickTi = 'yes';
            } else {
                sortTable(array, 1, DESC, type);
                upTi.style.display = 'block';
                clickTi = 'no';
            }

            break;
        case 'Type':
            if (clickTy == 'no') {
                sortTable(array, 2, ASC, type);
                downTy.style.display = 'block';
                clickTy = 'yes';
            } else {
                sortTable(array, 2, DESC, type);
                upTy.style.display = 'block';
                clickTy = 'no';
            }
            break;
        case 'Invite':
            if (clickI == 'no') {
                sortTable(array, 3, ASC, type);
                downI.style.display = 'block';
                clickI = 'yes';
            } else {
                sortTable(array, 3, DESC, type);
                upI.style.display = 'block';
                clickI = 'no';
            }
            break;

        case 'Label':
            if (clickL == 'no') {
                sortTable(array, 2, ASC, type);
                downL.style.display = 'block';
                clickL = 'yes';
            } else {
                sortTable(array, 2, DESC, type);
                upL.style.display = 'block';
                clickL = 'no';
            }
            break;
        case 'kind':
            if (clickT == 'no') {
                sortTable(array, 0, ASC, type);
                downT.style.display = 'block';
                clickT = 'yes';
            } else {
                sortTable(array, 0, DESC, type);
                upT.style.display = 'block';
                clickT = 'no';
            }
            break;

        case 'user':
            if (clickU == 'no') {
                sortTable(array, 0, ASC, type);
                downU.style.display = 'block';
                clickU = 'yes';
            } else {
                sortTable(array, 0, DESC, type);
                upU.style.display = 'block';
                clickU = 'no';
            }
            break;
        case 'numPaper':
            if (clickN == 'no') {
                sortTable(array, 1, NUMA, type);
                downN.style.display = 'block';
                clickN = 'yes';
            } else {
                sortTable(array, 1, NUMD, type);
                upN.style.display = 'block';
                clickN = 'no';
            }
            break;
        case 'startDate':
            if (clickS == 'no') {
                sortTable(array, 2, DATEA, type);
                downS.style.display = 'block';
                clickS = 'yes';
            } else {
                sortTable(array, 2, DATED, type);
                upS.style.display = 'block';
                clickS = 'no';
            }
            break;
        case 'endDate':
            if (clickE == 'no') {
                sortTable(array, 3, DATEA, type);
                downE.style.display = 'block';
                clickE = 'yes';
            } else {
                sortTable(array, 3, DATED, type);
                upE.style.display = 'block';
                clickE = 'no';
            }
            break;

            case 'pseudo':
            if (clickSps == 'no') {
                sortTable(array, 0, ASC, type);
                downSps.style.display = 'block';
                clickSps = 'yes';
            } else {
                sortTable(array, 0, DESC, type);
                upSps.style.display = 'block';
                clickSps = 'no';
            }
            break;
        case 'name':
            if (clickSn == 'no') {
                sortTable(array, 1, ASC, type);
                downSn.style.display = 'block';
                clickSn = 'yes';
            } else {
                sortTable(array, 1, DESC, type);
                upSn.style.display = 'block';
                clickSn = 'no';
            }
            break;
        case 'fname':
            if (clickSp == 'no') {
                sortTable(array, 2, ASC, type);
                downSp.style.display = 'block';
                clickSp = 'yes';
            } else {
                sortTable(array, 2, DESC, type);
                upSp.style.display = 'block';
                clickSp = 'no';
            }
            break;

        case 'dateL':
            if (clickDl == 'no') {
                sortTable(array, 0, DATEA, type);
                downDl.style.display = 'block';
                clickDl = 'yes';
            } else {
                sortTable(array, 0, DATED, type);
                upDl.style.display = 'block';
                clickDl = 'no';
            }
            break;
        case 'titleL':
            if (clickTl == 'no') {
                sortTable(array, 1, ASC, type);
                downTl.style.display = 'block';
                clickTl = 'yes';
            } else {
                sortTable(array, 1, DESC, type);
                upTl.style.display = 'block';
                clickTl = 'no';
            }
            break;
        case 'QuestionL':
            if (clickQl == 'no') {
                sortTable(array, 2, ASC, type);
                downQl.style.display = 'block';
                clickQl = 'yes';
            } else {
                sortTable(array, 2, DESC, type);
                upQl.style.display = 'block';
                clickQl = 'no';
            }
            break;
        case 'CategoryL':
            if (clickCl == 'no') {
                sortTable(array, 3, ASC, type);
                downCl.style.display = 'block';
                clickCl = 'yes';
            } else {
                sortTable(array, 3, DESC, type);
                upCl.style.display = 'block';
                clickCl = 'no';
            }
            break;
        case 'paperL':
            if (clickPl == 'no') {
                sortTable(array, 4, ASC, type);
                downPl.style.display = 'block';
                clickPl = 'yes';
            } else {
                sortTable(array, 4, DESC, type);
                upPl.style.display = 'block';
                clickPl = 'no';
            }
            break;
        case 'responseL':
            if (clickRl == 'no') {
                sortTable(array, 5, ASC, type);
                downRl.style.display = 'block';
                clickRl = 'yes';
            } else {
                sortTable(array, 5, DESC, type);
                upRl.style.display = 'block';
                clickRl = 'no';
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
    return a - b;
}

// To sort decreasing
function NUMD(a, b) {
    return b - a;
}

function DATE(x){
    var datex, yx, mx, dx, hx, mix, sx;

    datex = x[1];

    dx = datex.substring(0, datex.indexOf('/'));
    mx = datex.substring(datex.indexOf('/') + 1, datex.indexOf('/') + 3);
    yx = datex.substring(datex.lastIndexOf('/') + 1, datex.indexOf('-') - 1);
    hx = datex.substring(datex.indexOf('-') + 2, datex.indexOf('h'));
    mix = datex.substring(datex.indexOf('h') + 1, datex.indexOf('m'));
    sx = datex.substring(datex.indexOf('m') + 1, datex.indexOf('s'));

    dx = new Date(yx, (parseInt(mx) - 1), dx, hx, mix, sx);

    return (dx);
}

function DATEA(a, b){
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

function DATED(a, b){
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

// To display the rows in the right order
function sortTable(tid, col, ord, type) {

    mybody = document.getElementById(tid).getElementsByTagName('tbody')[0]; // The array to sort
    lines = mybody.getElementsByTagName('tr'); // The rows of the array to sort

    var sorter = new Array(); // Javascript array to do the sorting

    sorter.length = 0;

    var i = -1;

    // Put the rows of the html array into the rows of the javascript array
    while (lines[++i]) {
        // Sort by class
        if (type == 'kind') {
            colIcon = lines[i].getElementsByTagName('td')[col].getElementsByTagName('i')[col].className;
            iconLabel = colIcon.substring(colIcon.indexOf('-')+1);

            sorter.push([lines[i], iconLabel]);
        // Sort numbers
        } else if (type == 'numPaper'){
            sorter.push([lines[i].getElementsByTagName('td')[col].textContent.toLowerCase()]);
        // Sort string
        } else {
            sorter.push([lines[i], lines[i].getElementsByTagName('td')[col].textContent.toLowerCase()]);
        }

    }

    // Sort the rows
    sorter.sort(ord);

    // If numbers, get only numbers at first and after sort, get the entire row to sort it right
    if (type == 'numPaper') {
        for (var x = 0 ; x < sorter.length ; x++) {
           for (var z = 0 ; z < sorter.length ; z++ ) {
               if (sorter[x] == lines[z].getElementsByTagName('td')[col].textContent.toLowerCase()) {
                   sorter[x] = [lines[z], lines[z].getElementsByTagName('td')[col].textContent.toLowerCase()];
               }
            }
        }
    }

    j = -1;

    // Rearrange the html array's rows accordind to the sorting
    while(sorter[++j]) {
        mybody.appendChild(sorter[j][0]);
    }
}

// To search questions (with parameters in all the user's questions)
function searchQuestion(path, page) {

    var whatToFind = document.getElementById('what2search').value; // The text to find
    var type; // What column is selected (category, type, title, contain)
    var where; // In which database of questions (user's or shared)

    // Which type is selected ?
    if (document.getElementById('searchQuestionForm').QuestionSearch[0].checked) {
        type = document.getElementById('searchQuestionForm').QuestionSearch[0].value;
    } else if (document.getElementById('searchQuestionForm').QuestionSearch[1].checked) {
        type = document.getElementById('searchQuestionForm').QuestionSearch[1].value;
    } else if (document.getElementById('searchQuestionForm').QuestionSearch[2].checked) {
        type = document.getElementById('searchQuestionForm').QuestionSearch[2].value;
    } else if (document.getElementById('searchQuestionForm').QuestionSearch[3].checked) {
        type = document.getElementById('searchQuestionForm').QuestionSearch[3].value;
    }

    // In which database ?
    if (document.getElementById('searchQuestionForm').WhereSearch[0].checked) {
        where = document.getElementById('searchQuestionForm').WhereSearch[0].value;
    } else if (document.getElementById('searchQuestionForm').WhereSearch[1].checked) {
        where = document.getElementById('searchQuestionForm').WhereSearch[1].value;
    }

    // Send theses informations to the controller to have the matching questions and display it
    $.ajax({
        type: 'GET',
        url: path,
        data: {
            type : type,
            whatToFind : whatToFind,
            where : where,
            page: page
        },
        cache: false,
        success: function (data) {
          document.getElementById('resultSearch').innerHTML = data;
       }
    });
}

// If user change page after search, this function keep the parameters of the research and display it on the new page
window.onload = function () {

    // If has already done a question research
    if (document.getElementById('type') && document.getElementById('whatToFind') && document.getElementById('where')) {
        // What did he selected before changing page
        var type = document.getElementById('type').value;
        var whatToFind = document.getElementById('whatToFind').value;
        var where = document.getElementById('where').value;

        // Select and put the right old user's choices
        document.getElementById('what2search').value = whatToFind;

        if (type == 'Category') {
            document.getElementById('searchQuestionForm').QuestionSearch[0].checked = true;
        } else if (type == 'Type') {
            document.getElementById('searchQuestionForm').QuestionSearch[1].checked = true;
        } else if (type == 'Title') {
            document.getElementById('searchQuestionForm').QuestionSearch[2].checked = true;
        } else if (type == 'Contain') {
            document.getElementById('searchQuestionForm').QuestionSearch[3].checked = true;
        }

        if (where == 'my') {
            document.getElementById('searchQuestionForm').WhereSearch[0].checked = true;
        } else if (where == 'shared') {
            document.getElementById('searchQuestionForm').WhereSearch[1].checked = true;
        }
    }

    // If has already done a document research, put the values of the research and display selected block when changing page
    if (document.getElementById('label2Find') && document.getElementById('whichAction')) {
        if (document.getElementById('label2Find').value != '') {
            document.getElementById('labelToFind').value = document.getElementById('label2Find').value;
            document.getElementById('searchDocuments').style.display = 'block';
        }

        if (document.getElementById('whichAction').value == 'sort') {
            document.getElementById('sortDocuments').style.display = 'block';
        }
    }
};