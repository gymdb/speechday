function setTab() {
    var pageNameElem = $('#pageName');
    if (pageNameElem.length > 0) {
        var pageName = pageNameElem[0].innerText;
        var tabName = '#navTab' + pageName;
        $(tabName).addClass('active');
    }
}

function showMessage(element, type, message) {
    element.html('<div class="alert alert-' + type + '">' + message + '</div>');
    element.show();
    element.delay(5000).fadeOut(400);
}

function PrintElem(elem) {
    Popup($(elem).html());
}

function Popup(data) {
    var printWindow = window.open('www.dachsberg.at/ESV', 'Mein Elternsprechtag', '');
    var doc = printWindow.document;
    doc.write("<html><head><title>Mein Elternsprechtag</title>");
    doc.write("<link href='css/print.css' rel='stylesheet' type='text/css' media='print' />");
    doc.write("</head><body>");
    doc.write(data);
    doc.write("</body></html>");
    doc.close();

    function show() {
        if (doc.readyState === "complete") {
            printWindow.document.close();
            printWindow.focus();
            printWindow.print();
            printWindow.close();
        } else {
            setTimeout(show, 100);
        }
    }

    show();
}