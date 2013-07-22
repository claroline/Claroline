function AddDocument() {
    if (document.getElementById('AddDocuments').style.display == 'none') {
        document.getElementById('AddDocuments').style.display = 'block';
        document.getElementById('icon').className = 'icon-minus';
    } else {
        document.getElementById('AddDocuments').style.display = 'none';
        document.getElementById('icon').className = 'icon-plus';
    }
}

function ChangeName(oldname) {
    if (document.getElementById('UpdateName').style.display == 'none') {
        document.getElementById('UpdateName').style.display = 'block';
        document.getElementById('oldName').value = oldname;
    } else {
        document.getElementById('UpdateName').style.display = 'none';
        document.getElementById('newlabel').value = '';
        document.getElementById('updateSubmit').disabled = false;
        document.getElementById('oldName').value = '';
    }
}

function sortDoc() {
    if (document.getElementById('sortDocuments').style.display == 'none') {
        document.getElementById('sortDocuments').style.display = 'block';
    } else {
        document.getElementById('sortDocuments').style.display = 'none';
    }
}

function searchDocuments() {
    if (document.getElementById('searchDocuments').style.display == 'none') {
        document.getElementById('searchDocuments').style.display = 'block';
    } else {
        document.getElementById('searchDocuments').style.display = 'none';
    }
}

function sortDocument(type, path, page) {
    // Send the type to display the matching documents

    var searchLabel;

    if (document.getElementById('labelToFind').value) {
        searchLabel = document.getElementById('labelToFind').value;
    } else {
        searchLabel = '';
    }


    $.ajax({
        type: 'GET',
        url: path,
        data: {
            doctype : type,
            searchLabel: searchLabel,
            page: page
        },
        cache: false,
        success: function (data) {
            document.getElementById('sorting').innerHTML = data;
        }
    });
}

function searchDoc(path, page) {

    var labelToFind = document.getElementById('labelToFind').value;

    $.ajax({
        type: 'GET',
        url: path,
        data: {
            labelToFind : labelToFind,
            page: page
        },
        cache: false,
        success: function (data) {
            document.getElementById('sorting').innerHTML = data;
        }
    });
}