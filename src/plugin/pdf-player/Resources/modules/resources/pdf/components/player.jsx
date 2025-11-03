import React, {Component} from 'react'
import {PropTypes as T} from 'prop-types'
import get from 'lodash/get'
import isEmpty from 'lodash/isEmpty'
import isNumber from 'lodash/isNumber'
import classes from 'classnames'

import * as pdfjs from 'pdfjs-dist/build/pdf'
import {
  EventBus,
  PDFLinkService,
  PDFViewer,
  ScrollMode
} from 'pdfjs-dist/web/pdf_viewer'
pdfjs.GlobalWorkerOptions.workerSrc = new URL(
  'pdfjs-dist/build/pdf.worker.min.mjs',
  import.meta.url
).toString()

import {url} from '#/main/app/api'
import {toKey} from '#/main/app/utils/text'
import {trans, transChoice} from '#/main/app/intl/translation'
import {Button} from '#/main/app/action/components/button'
import {ASYNC_BUTTON, CALLBACK_BUTTON, MENU_BUTTON} from '#/main/app/buttons'
import {Select} from '#/main/app/input/components/select'
import {Tree} from '#/main/app/components/tree'
import {Menu} from '#/main/app/overlays/menu'
import {PageContent} from '#/main/app/page'
import {ResourcePage} from '#/main/core/resource'

const MIN_SCALE = 10
const BASE_SCALE = 100
const MAX_SCALE = 1000
const DEFAULT_SCALE = 'auto'
const DEFAULT_SCROLL_MODE = ScrollMode.PAGE

const SCALES = {
  'auto': trans('pdf_zoom_auto', {}, 'resource'),
  'page-actual': trans('pdf_zoom_actual', {}, 'resource'),
  'page-fit': trans('pdf_zoom_fit', {}, 'resource'),
  'page-width': trans('pdf_zoom_width', {}, 'resource'),
  50: trans('percent', {value: 50}),
  75: trans('percent', {value: 75}),
  100: trans('percent', {value: 100}),
  125: trans('percent', {value: 125}),
  150: trans('percent', {value: 150}),
  200: trans('percent', {value: 200}),
  300: trans('percent', {value: 300}),
  400: trans('percent', {value: 400})
}

const PdfSummary = ({
  summary
}) => {
  function getPageSummary(page) {
    return {
      id: toKey(page.label),
      type: CALLBACK_BUTTON,
      label: page.label,
      callback: page.callback,
      children: page.children ? page.children.map(getPageSummary) : []
    }
  }

  return (
    <Menu className="p-3 scroller-y scroller-thin" style={{minWidth: '20rem', maxHeight: '80vh'}}>
      <Tree
        items={summary.map(getPageSummary)}
        size="sm"
      />
    </Menu>
  )
}

PdfSummary.propTypes = {
  summary: T.arrayOf(T.shape({
    id: T.string,
    label: T.string,
    children: T.arrayOf(T.object),
    callback: T.func
  }))
}

const PdfMenu = (props) => {
  const baseScale = isNumber(props.scaleValue) ? props.scaleValue : BASE_SCALE

  return (
    <div
      className={classes('pdf-menu px-3 py-1 bg-body-tertiary d-flex flex-row flex-wrap align-items-center gap-2', {
        'sticky-xxl-top border-bottom': !props.embedded,
        'rounded-3': props.embedded
      })}
      role="roolbar"
    >
      <div className="" role="presentation">
        {!isEmpty(props.summary) &&
          <Button
            className="btn btn-text-body p-2 py-1 focus-ring"
            type={MENU_BUTTON}
            icon="fa fa-fw fa-list"
            label={trans('summary')}
            menu={
              <PdfSummary summary={props.summary} />
            }
            tooltip="bottom"
          />
        }

        <Button
          className="btn btn-text-body p-2 py-1 focus-ring"
          type={CALLBACK_BUTTON}
          icon="fa fa-fw fa-chevron-left"
          label={trans('previous')}
          disabled={!props.currentPage || 1 >= props.currentPage}
          callback={() => props.changePage(props.currentPage - 1)}
          tooltip="bottom"
        />

        <Button
          className="btn btn-text-body p-2 py-1 focus-ring"
          type={CALLBACK_BUTTON}
          icon="fa fa-fw fa-chevron-right"
          label={trans('next')}
          disabled={!props.currentPage || props.pages <= props.currentPage}
          callback={() => props.changePage(props.currentPage + 1)}
          tooltip="bottom"
        />
      </div>

      <div className="input-group input-group-sm w-auto" role="presentation">
        <input
          type="number"
          className="form-control"
          value={props.currentPage}
          min={1}
          max={props.pages}
          onChange={(e) => props.changePage(e.currentTarget.value)}
        />

        <span className="input-group-text" role="presentation">
          {transChoice('count_pages', props.pages, {count: props.pages}, 'resource')}
        </span>
      </div>

      <div className="ms-auto">
        <Button
          className="btn btn-text-body p-2 py-1 focus-ring"
          type={CALLBACK_BUTTON}
          icon="fa fa-fw fa-minus"
          label={trans('zoom_out')}
          callback={() => props.zoom(baseScale - 10)}
          disabled={MIN_SCALE === baseScale}
          tooltip="bottom"
        />

        <Button
          className="btn btn-text-body p-2 py-1 focus-ring"
          type={CALLBACK_BUTTON}
          icon="fa fa-fw fa-plus"
          label={trans('zoom_in')}
          callback={() => props.zoom(baseScale + 10)}
          disabled={MAX_SCALE === baseScale}
          tooltip="bottom"
        />
      </div>

      <Select
        id={props.nodeId+'-zoom'}
        className="w-auto"
        choices={SCALES}
        size="sm"
        onChange={props.scale}
        value={props.scaleValue}
        noEmpty={true}
      />

      {!props.embedded &&
        <Button
          className="btn btn-text-body p-2 py-1 focus-ring"
          type={MENU_BUTTON}
          icon={classes('fa fa-fw', {
            'fa-file': ScrollMode.PAGE === props.scrollMode,
            'fa-up-down': ScrollMode.VERTICAL === props.scrollMode,
            'fa-left-right': ScrollMode.HORIZONTAL === props.scrollMode
          })}
          label={trans('pdf_scroll_mode', {}, 'resource')}
          tooltip="bottom"
          menu={{
            items: [ScrollMode.PAGE, ScrollMode.VERTICAL, ScrollMode.HORIZONTAL].map((scrollMode) => ({
              name: scrollMode,
              type: CALLBACK_BUTTON,
              icon: classes('fa fa-fw', {
                'fa-file': ScrollMode.PAGE === scrollMode,
                'fa-up-down': ScrollMode.VERTICAL === scrollMode,
                'fa-left-right': ScrollMode.HORIZONTAL === scrollMode
              }),
              label: classes({
                [trans('pdf_scroll_page', {}, 'resource')]: ScrollMode.PAGE === scrollMode,
                [trans('pdf_scroll_vertical', {}, 'resource')]: ScrollMode.VERTICAL === scrollMode,
                [trans('pdf_scroll_horizontal', {}, 'resource')]: ScrollMode.HORIZONTAL === scrollMode
              }),
              callback: () => props.changeScrollMode(scrollMode)
            }))
          }}
        />
      }

      {props.downloadable &&
        <Button
          className="btn btn-text-body p-2 py-1 focus-ring"
          type={ASYNC_BUTTON}
          icon="fa fa-fw fa-file-download"
          label={trans('download', {}, 'actions')}
          tooltip="bottom"
          request={{
            url: url(['claro_resource_download'], {ids: [props.nodeId]})
          }}
        />
      }
    </div>
  )
}

PdfMenu.propTypes = {
  embedded: T.bool,
  nodeId: T.string.isRequired,
  summary: T.arrayOf(T.shape({
    id: T.string,
    label: T.string,
    children: T.arrayOf(T.object)
  })),
  downloadable: T.bool,

  currentPage: T.number,
  pages: T.number,
  changePage: T.func.isRequired,

  scaleValue: T.oneOfType([T.string, T.number]).isRequired,
  scale: T.func.isRequired,
  zoom: T.func.isRequired,

  scrollMode: T.string.isRequired,
  changeScrollMode: T.func.isRequired
}

class PdfPlayer extends Component {
  constructor(props) {
    super(props)

    this.state = {
      viewer: null,
      page: 1,
      pages: 1,
      scale: DEFAULT_SCALE,
      scrollMode: DEFAULT_SCROLL_MODE,
      summary: []
    }

    this.resize = this.resize.bind(this)
    this.zoom = this.zoom.bind(this)
    this.scale = this.scale.bind(this)
    this.renderPage = this.renderPage.bind(this)
    this.changeScrollMode = this.changeScrollMode.bind(this)
  }

  componentDidMount() {
    const eventBus = new EventBus()

    // enable hyperlinks within PDF files.
    const pdfLinkService = new PDFLinkService({eventBus})

    // PDFViewer
    const pdfViewer = new PDFViewer({
      container: document.getElementById('pdf-' + this.props.nodeId),
      viewerContainer: document.getElementById('viewer-' + this.props.nodeId),
      eventBus: eventBus,
      linkService: pdfLinkService
    })
    pdfLinkService.setViewer(pdfViewer)

    eventBus.on('pagerendered', this.resize)

    eventBus.on('pagechanging', (event) => {
      this.renderPage(event.pageNumber)
    })

    eventBus.on('pagesinit', () => {
      this.resize()
      this.renderPage(1)
    })

    this.props.loadFile(url(['apiv2_pdf_file', {id: this.props.nodeId}])).then((fileData) => {
      const loadingTask = pdfjs.getDocument({
        url: URL.createObjectURL(fileData)
      })

      loadingTask.promise.then((pdf) => {
        pdfViewer.setDocument(pdf)
        pdfLinkService.setDocument(pdf, null)

        pdf.getOutline().then((outline) => {
          if (outline) {
            const getItem = (item) => ({
              // id: pdfViewer.pageLabelToPageNumber(item.title),
              label: item.title,
              children: item.items ? item.items.map(getItem) : [],
              callback: () => pdfLinkService.goToDestination(item.dest)
            })

            this.setState({summary: outline.map(getItem)})
          }
        })

        this.setState({
          pages: pdf.numPages,
          viewer: pdfViewer
        })
      })
    })
  }

  resize() {
    if (!this.state.viewer) {
      return
    }

    this.state.viewer.scrollMode = this.state.scrollMode
    this.state.viewer.currentScaleValue = this.state.scale
  }

  renderPage(pageNumber) {
    this.setState({page: parseInt(pageNumber)})
    if (this.props.currentUser) {
      this.props.updateProgression(this.props.nodeId, pageNumber, this.state.pages)
    }
  }

  changePage(pdfViewer, requestPageNum) {
    let pageNum = requestPageNum

    if (!pageNum || 1 >= pageNum) {
      pageNum = 1
    } else if (pageNum > this.state.pages) {
      pageNum = this.state.pages
    }

    pdfViewer.currentPageNumber = parseInt(pageNum)
  }

  zoom(requestScale) {
    let scale = parseInt(requestScale)
    if (scale < MIN_SCALE) {
      scale = MIN_SCALE
    }

    if (scale > MAX_SCALE) {
      scale = MAX_SCALE
    }

    this.scale(scale)
  }

  scale(scale) {
    if (isNumber(scale)) {
      scale = parseInt(scale) / 100
    }
    this.setState({scale: scale}, this.resize)
  }

  changeScrollMode(scrollMode) {
    this.setState({scrollMode: scrollMode}, this.resize)
  }

  render() {
    return (
      <ResourcePage>
        <PageContent className="z-0 d-flex flex-column">
          <PdfMenu
            nodeId={this.props.nodeId}
            embedded={this.props.embedded}
            summary={this.state.summary}
            currentPage={this.state.page}
            pages={this.state.pages}
            changePage={(newPage) => this.changePage(this.state.viewer, newPage)}
            scaleValue={isNumber(this.state.scale) ? this.state.scale * 100 : this.state.scale}
            zoom={this.zoom}
            scale={this.scale}
            scrollMode={this.state.scrollMode}
            changeScrollMode={this.changeScrollMode}
            downloadable={this.props.downloadable}
          />

          <div
            className="pdf-container position-relative w-100 flex-fill"
            role="presentation"
            style={{height: get(this.state.viewer, 'container.offsetHeight')}}
          >
            <div
              id={'pdf-' + this.props.nodeId}
              className={classes('pdf-content position-absolute w-100', {
                'h-100': !this.props.embedded,
                'scroller-thin': this.props.embedded
              })}
            >
              <div
                id={'viewer-' + this.props.nodeId}
                className="pdfViewer"
              />
            </div>
          </div>
        </PageContent>
      </ResourcePage>
    )
  }
}

PdfPlayer.propTypes = {
  nodeId: T.string.isRequired,
  downloadable: T.bool.isRequired,
  embedded: T.bool,
  updateProgression: T.func.isRequired,
  currentUser: T.object,
  loadFile: T.func.isRequired
}

export {
  PdfPlayer
}
