// Display block to add a new document
function AddDocument() {
    if ($('#AddDocuments').css({"display" : "none"})) {
        $('#AddDocuments').css({"display" : "block"});
        $('#icon').attr('class', 'icon-minus');
    } else {
        $('#AddDocuments').css({"display" : "none"});
        $('#icon').attr('class', 'icon-plus');
    }
}

// Display pop up to change name of a document
function ChangeName(oldname) {
    if ($('#UpdateName').css({"display" : "none"})) {
        $('#UpdateName').css({"display" : "block"});
        $('#oldName').val(oldname);
    } else {
        $('#UpdateName').css({"display" : "none"});
        $('#newlabel').val('');
        $('#updateSubmit').prop('disabled', false);
        $('#oldName').val('');
    }
}

// Display block to sort documents
function sortDoc() {
    if ($('#sortDocuments').css({"display" : "none"})) {
        $('#sortDocuments').css({"display" : "block"});
    } else {
        $('#sortDocuments').css({"display" : "none"});
    }
}

// Display block to search documents
function searchDocuments() {
    if ($('#searchDocuments').css({"display" : "none"})) {
        $('#searchDocuments').css({"display" : "block"});
    } else {
        $('#searchDocuments').css({"display" : "none"});
    }
}

// Sort documents depending on its type
function sortDocument(type, path, page) {

    var searchLabel;

    // If have to sort the search documents
    if ($('#labelToFind').length > 0) {
        searchLabel = $('#labelToFind').val();
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
            $('#sorting').html(data);
        }
    });
}

// Search documents with specific label
function searchDoc(path, page) {

    var labelToFind = $('#labelToFind').val();

    $.ajax({
        type: 'GET',
        url: path,
        data: {
            labelToFind : labelToFind,
            page: page
        },
        cache: false,
        success: function (data) {
            $('#sorting').html(data);
        }
    });
}