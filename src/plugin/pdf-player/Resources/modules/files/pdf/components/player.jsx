import React, {Component, Fragment} from 'react'
import {PropTypes as T} from 'prop-types'
import {PDFJS} from 'pdfjs-dist/build/pdf.combined'

import {trans, transChoice} from '#/main/app/intl/translation'
import {ContentLoader} from '#/main/app/content/components/loader'

import {Pdf as PdfTypes} from '#/plugin/pdf-player/files/pdf/prop-types'

class PdfPlayer extends Component {
  constructor(props) {
    super(props)
    this.state = {
      loaded: false,
      pdf: null,
      page: 1,
      scale: 100,
      context: null
    }
  }

  componentDidMount() {
    PDFJS.disableRange = true
    PDFJS.disableWorker = true
    PDFJS.getDocument(this.props.file.url).then((pdf) => {
      this.setState({
        loaded: true,
        pdf: pdf,
        context: document.getElementById('pdf-canvas-' + this.props.file.id).getContext('2d')
      }, () => this.renderPage())
    }).catch(() => {})
  }

  renderPage(updateProgression = true) {
    this.state.pdf.getPage(this.state.page).then(page => {
      const viewport = page.getViewport(this.state.scale / 100)
      const canvas = document.getElementById('pdf-canvas-' + this.props.file.id)
      canvas.height = viewport.height
      canvas.width = viewport.width

      // Render PDF page into canvas context
      const renderContext = {
        canvasContext: this.state.context,
        viewport: viewport
      }
      page.render(renderContext)

      if (this.props.currentUser && updateProgression) {
        this.props.updateProgression(this.props.file.id, this.state.page, this.state.pdf.numPages)
      }
    })
  }

  changePage(requestPageNum) {
    let pageNum = requestPageNum

    if (!pageNum || 1 >= pageNum) {
      pageNum = 1
    } else if (pageNum > this.state.pdf.numPages) {
      pageNum = this.state.pdf.numPages
    }

    this.setState({page: parseInt(pageNum)}, () => this.renderPage())
  }

  zoom(requestScale) {
    let scale = requestScale

    if (1 >= scale) {
      scale = 1
    }
    this.setState({scale: parseInt(scale)}, () => this.renderPage(false))
  }

  render() {
    // NB : canvas is only hidden while loading because we need the DOM node to be present to bind the pdf renderer
    return (
      <Fragment>
        {!this.state.loaded &&
          <ContentLoader
            className="row"
            size="lg"
            description={trans('loading', {}, 'file')}
          />
        }

        {this.state.loaded &&
          <div className="row">
            <div className="pdf-player-menu">
              <div className="pdf-pages">
                <button
                  className="btn btn-link-default"
                  disabled={!this.state.page || 1 >= this.state.page}
                  onClick={() => this.changePage(this.state.page - 1)}
                >
                  <span className="fa fa-fw fa-backward"/>
                </button>
                <button
                  className="btn btn-link-default"
                  disabled={!this.state.pdf || !this.state.page || this.state.pdf.numPages <= this.state.page}
                  onClick={() => this.changePage(this.state.page + 1)}
                >
                  <span className="fa fa-fw fa-forward"/>
                </button>

                <input
                  type="number"
                  className="form-control input-sm"
                  value={this.state.page}
                  onChange={(e) => this.changePage(e.currentTarget.value)}
                />
                {transChoice('count_pages', this.state.pdf ? this.state.pdf.numPages : 0, {count: this.state.pdf ? this.state.pdf.numPages : 0}, 'resource')}
              </div>

              <div className="pdf-zoom">
                <button
                  disabled={!this.state.pdf || !this.state.scale}
                  className="btn btn-link-default"
                  onClick={() => this.zoom(this.state.scale + 25)}
                >
                  <span className="fa fa-fw fa-search-plus"/>
                </button>
                <button
                  className="btn btn-link-default"
                  disabled={!this.state.pdf || !this.state.scale || 1 >= this.state.scale}
                  onClick={() => this.zoom(this.state.scale - 25)}
                >
                  <span className="fa fa-fw fa-search-minus"/>
                </button>

                <input
                  type="number"
                  min="5"
                  className="form-control input-sm"
                  value={this.state.scale}
                  onChange={(e) => this.zoom(e.currentTarget.value)}
                />
                <span className="pdf-zoom-unit">%</span>
              </div>
            </div>
          </div>
        }

        <div className="pdf-player component-container" style={!this.state.loaded ? {display: 'none'} : {}}>
          <canvas id={'pdf-canvas-' + this.props.file.id} className="pdf-player-page" />
        </div>
      </Fragment>
    )
  }
}

PdfPlayer.propTypes = {
  file: T.shape(
    PdfTypes.propTypes
  ).isRequired,
  updateProgression: T.func.isRequired,
  currentUser: T.object
}

export {
  PdfPlayer
}
