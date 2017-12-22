import * as pdf from 'pdfjs-dist/build/pdf.combined'

export default class PdfController {
  constructor($http, $scope, url) {
    this.isLoading = true
    this.pageNum = 1
    this.$http = $http
    this.$scope = $scope
    this.UrlGenerator = url

    pdf.PDFJS.disableWorker = true
    this.pdfDoc = null
    this.scale = 100
    this.canvas = document.getElementById('the-canvas')
    if (parseInt(this.download) !== 1) {
      this.disableContextMenu(this.canvas)
    }
    this.ctx = this.canvas.getContext('2d')

    this.renderPdf()
  }

  renderPage() {
    this.pageNum = parseInt(this.pageNum)
    if (this.pageNum <= 1 || !this.pageNum) this.pageNum = 1
    if (this.pageNum > this.pdfDoc.numPages) this.pageNum = this.pdfDoc.numPages
    // Using promise to fetch the page
    this.pdfDoc.getPage(this.pageNum).then(page => {
      const viewport = page.getViewport(this.scale / 100)
      this.canvas.height = viewport.height
      this.canvas.width = viewport.width

      // Render PDF page into canvas context
      const renderContext = {
        canvasContext: this.ctx,
        viewport: viewport
      }
      page.render(renderContext)
    })
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
    this.scale += 25
    this.renderPage()
  }

  zoomOut() {
    this.scale -= 25
    if (5 > this.scale) {
      this.scale = 5
    }

    this.renderPage()
  }

  renderPdf() {
    pdf.PDFJS.getDocument(this.url).then(_pdfDoc => {
      this.pdfDoc = _pdfDoc
      this.isLoading = false
      this.$scope.$apply()
      this.renderPage(this.pageNum)
    })
  }

  disableContextMenu(el) {
    if (el.addEventListener) {
      el.addEventListener('contextmenu', (e) => {
        e.preventDefault()
      }, false)
    } else {
      el.attachEvent('oncontextmenu', () => {
        window.event.returnValue = false
      })
    }
  }
}
