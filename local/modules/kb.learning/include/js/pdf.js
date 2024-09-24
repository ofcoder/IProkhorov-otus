function getCertificate(userFio, date) {
    printPdf(createPdf(userFio, date));
}

function printPdf(element) {
    const opt = {
        margin:             0,
        filename:           'myfile.pdf',
        image:              { type: 'jpeg', quality: 0.98 },
        html2canvas:        { scale: 5 },
        jsPDF:              { unit: 'pt', format: 'a4', orientation: 'portrait' },
        documentProperties: { title: 'Certificate PDF' }
    };

    html3pdf().from(element).set(opt).toContainer().outputPdf().get('pdf').then(function (pdfObj) {
        window.open(pdfObj.output("bloburl"), "F");
    });
}

function createPdf(userFio, date) {
    const element = document.getElementById('user-certificate');
    document.getElementById('certificate-user-name').innerText = userFio;
    document.getElementById('certificate-date').innerText = date;
    return element;
}