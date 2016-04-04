import * as pdf from 'pdfjs-dist/build/pdf.combined'

export default class PdfController {
	constructor($http, $scope) {
		this.pageNum = 1
		this.$http = $http
        this.$scope = $scope

        pdf.PDFJS.disableWorker = true;
        this.pdfDoc = null
        this.scale = 1,
        this.canvas = document.getElementById('the-canvas')
        this.ctx = this.canvas.getContext('2d')
        this.node = null
        this.downloadUri = '#'
        //q&dirty
        $http.get(Routing.generate('api_get_resource_node', {'resourceNode': AngularApp.nodeId} )).then(d => {
        	this.node = d.data
        	this.url = Routing.generate("claro_pdf", {"node": this.node.id})
        	this.downloadUri = Routing.generate('claro_resource_download') + '?ids[]=' + this.node.id
        	this.renderPdf()
        });
	}

   renderPage() {
   		this.pageNum = parseInt(this.pageNum)
   		if (this.pageNum <= 1 || !this.pageNum) this.pageNum = 1
   		if (this.pageNum > this.pdfDoc.numPages) this.pageNum = this.pdfDoc.numPages
        // Using promise to fetch the page
        this.pdfDoc.getPage(this.pageNum).then(page => {
            const viewport = page.getViewport(this.scale);
            this.canvas.height = viewport.height;
            this.canvas.width = viewport.width;

            // Render PDF page into canvas context
            const renderContext = {
                canvasContext: this.ctx,
                viewport: viewport
            };
            page.render(renderContext);
        });
    }

    goPrevious() {
        if (this.pageNum <= 1)
            return
        this.pageNum--
        this.renderPage()
    }

    goNext() {
        if (this.pageNum >= this.pdfDoc.numPages)
            return
        this.pageNum++
        this.renderPage()
    }

    zoomIn() {
        this.scale*=1.1
        this.renderPdf()
    }

    zoomOut() {
        this.scale*=0.9
        this.renderPdf()
    }

    renderPdf() {
        pdf.PDFJS.getDocument(this.url).then(_pdfDoc => {
            this.pdfDoc = _pdfDoc
            this.$scope.$apply()
            this.renderPage(this.pageNum)
        });
    }
}