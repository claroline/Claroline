var clickC = clickTi = clickTy = clickI = 'no'; // To know if sorting is up or down

// Sort questions by selected column (type)
function SortQuestions(type, array) {

    var upC, upTi, upTy, upI, downC, downTi, downTy, downI; // Arrows to show the directions of the sorting

    // If two arrays in one page, select the matching arrows of the right array
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

    // Hide all arrows
    upC.style.display = 'none';
    upTi.style.display = 'none';
    upTy.style.display = 'none';
    upI.style.display = 'none';
    downC.style.display = 'none';
    downTi.style.display = 'none';
    downTy.style.display = 'none';
    downI.style.display = 'none';

    // Depend on which column were clicked, sort in the good direction and display the matching arrow
    switch (type) {
        case 'Category':
            if (clickC == 'no') {
                sortTable(array, 0, ASC);
                downC.style.display = 'block';
                clickC = 'yes';
            } else {
                sortTable(array, 0, DESC);
                upC.style.display = 'block';
                clickC = 'no';
            }
            break;
        case 'Title':
            if (clickTi == 'no') {
                sortTable(array, 1, ASC);
                downTi.style.display = 'block';
                clickTi = 'yes';
            } else {
                sortTable(array, 1, DESC);
                upTi.style.display = 'block';
                clickTi = 'no';
            }

            break;
        case 'Type':
            if (clickTy == 'no') {
                sortTable(array, 2, ASC);
                downTy.style.display = 'block';
                clickTy = 'yes';
            } else {
                sortTable(array, 2, DESC);
                upTy.style.display = 'block';
                clickTy = 'no';
            }
            break;
        case 'Invite':
            if (clickI == 'no') {
                sortTable(array, 3, ASC);
                downI.style.display = 'block';
                clickI = 'yes';
            } else {
                sortTable(array, 3, DESC);
                upI.style.display = 'block';
                clickI = 'no';
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

// To display the rows in the right order
function sortTable(tid, col, ord) {

    mybody = document.getElementById(tid).getElementsByTagName('tbody')[0]; // The array to sort
    lines = mybody.getElementsByTagName('tr'); // The rows of the array to sort

    var sorter = new Array(); // Javascript array to do the sorting

    sorter.length = 0;

    var i = -1;

    // Put the rows of the html array into the rows of the javascript array
    while (lines[++i]) {
        sorter.push([lines[i],lines[i].getElementsByTagName('td')[col].textContent.toLowerCase()]);
    }

    // Sort the rows
    sorter.sort(ord);

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

    // If has already done a research
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
};