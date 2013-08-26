window.onload = function () {

    if (document.getElementById('up0')) {
        mybody = document.getElementById('QuestionArray').getElementsByTagName('tbody')[0];
        lines = mybody.getElementsByTagName('tr');
        DisplayArrowOrder(lines);
    }
};

function changeOrderQuestion(sens, title) {

    title = title.replace("_", "'");

    mybody = document.getElementById('QuestionArray').getElementsByTagName('tbody')[0];
    lines = mybody.getElementsByTagName('tr');

    for (i = 0 ; i < lines.length ; i++) {
        if (lines[i].getElementsByTagName('td')[1].textContent == title) {
            oldIndex = i;
            break;
        }
    }

    if (sens == 'up') {
        newIndex = oldIndex - 1;
    } else {
        newIndex = oldIndex + 1;
    }

    var sorter = new Array();
    sorter.length = 0;

    if ((oldIndex) != 0) {
        before = lines[oldIndex - 1];
    }
    if ((oldIndex) != (lines.length - 1)) {
        after = lines[oldIndex + 1];
    }

    if (newIndex == 0) {
        sorter[0] = lines[oldIndex];
        sorter[1] = before;
        for (i = 2 ; i < lines.length ; i++) {
            sorter[i] = lines[i];
        }
    } else if (newIndex == lines.length) {
        sorter[lines.length] = lines[oldIndex];
        sorter[lines.length - 1] = after;
        for (i = lines.length - 2 ; i > 0 ; i--) {
            sorter[i] = lines[i];
        }
    } else {
        for (i = 0 ; i < oldIndex ; i++) {
            sorter[i] = lines[i];
        }

        temp = lines[newIndex];
        sorter[newIndex] = lines[oldIndex];
        sorter[oldIndex] = temp;

        if (sens == 'up') {
            for (i = oldIndex + 1 ; i < lines.length ; i++) {
                sorter[i] = lines[i];
            }
        } else {
            for (i = newIndex + 1; i < lines.length ; i++) {
                sorter[i] = lines[i];
            }
        }
    }

    j = -1;

    while(sorter[++j]) {
        mybody.appendChild(sorter[j]);
    }

    DisplayArrowOrder(lines);

    document.getElementById('SaveOrder').style.display = 'block';
}

function DisplayArrowOrder(lines) {
    for (i = 0 ; i < lines.length ; i++) {
        firstLine = lines[i].getElementsByTagName('td')[5].innerHTML;
        start = firstLine.substring(firstLine.indexOf('id="up') + 6, firstLine.indexOf('"></i>'));

        up = 'up' + start;
        down = 'down' + start;

        if (i == 0) {
            document.getElementById(up).style.display = 'none';
            document.getElementById(down).style.display = 'block';
        }else if (i == (lines.length - 1)) {
            document.getElementById(up).style.display = 'block';
            document.getElementById(down).style.display = 'none';
        } else {
            document.getElementById(up).style.display = 'block';
            document.getElementById(down).style.display = 'block';
        }
    }
}

function SaveNewOrder(path, exoID, length) {

    var order = new Array();

    mybody = document.getElementById('QuestionArray').getElementsByTagName('tbody')[0];
    lines = mybody.getElementsByTagName('tr');

    for (i = 0 ; i < lines.length ; i++) {
        order[i] = lines[i].getElementsByTagName('td')[6].textContent.trim();
    }

    $.ajax({
        type: 'POST',
        url: path,
        data: {
            exoID: exoID,
            order: order
        }
    });

    document.getElementById('SaveOrder').style.display = 'none';
}