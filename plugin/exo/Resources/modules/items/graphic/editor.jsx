import React, {Component, PropTypes as T} from 'react'
import get from 'lodash/get'
import {asset} from '#/main/core/asset'
import {tex} from './../../utils/translate'
import {makeDroppable} from './../../utils/dragAndDrop'
import {FileDropZone} from './../../components/form/file-drop-zone.jsx'
import {ErrorBlock} from './../../components/form/error-block.jsx'
import {ImageInput} from './components/image-input.jsx'
import {ModeSelector} from './components/mode-selector.jsx'
import {AreaPopover} from './components/area-popover.jsx'
import {ResizeDragLayer} from './components/resize-drag-layer.jsx'
import {AnswerAreaDraggable} from './components/answer-area.jsx'
import {actions} from './actions'
import {
  MODE_SELECT,
  MAX_IMG_SIZE,
  SHAPE_RECT,
  TYPE_ANSWER_AREA,
  TYPE_AREA_RESIZER
} from './enums'

let AnswerDropZone = props => props.connectDropTarget(props.children)

AnswerDropZone.propTypes = {
  connectDropTarget: T.func.isRequired,
  children: T.element.isRequired
}

AnswerDropZone = makeDroppable(AnswerDropZone, [
  TYPE_ANSWER_AREA,
  TYPE_AREA_RESIZER
])

export class Graphic extends Component {
  constructor(props) {
    super(props)
    this.onDropImage = this.onDropImage.bind(this)
    this.onSelectImage = this.onSelectImage.bind(this)
    this.onClickImage = this.onClickImage.bind(this)
    this.onResize = this.onResize.bind(this)
  }

  componentDidMount() {
    // because we need to access various DOM-related APIs to deal with the image
    // and because letting React compare the data/src attribute (which can be a
    // a very long string if the image is large) could be too heavy, the img tag
    // is rendered outside the React pipeline and just attached to a leaf node
    // of the component.
    if (this.props.item.image.data || this.props.item.image.url) {
      const img = this.createImage(this.props.item.image.data, this.props.item.image.url)
      img.onload = () => this.props.onChange(actions.resizeImage(img.width, img.height))
    } else {
      this.imgContainer.innerHTML = tex('graphic_drop_or_pick')
    }

    window.addEventListener('resize', this.onResize)

    this.imgContainer.addEventListener('click', () => {
      this.props.onChange(actions.blurAreas())
    })
  }

  componentDidUpdate(prevProps) {
    const img = this.imgContainer.querySelector('img')

    if (img) {
      img.className = this.props.item._mode !== MODE_SELECT ? 'point-mode' : ''

      if (prevProps.item.image.data !== this.props.item.image.data) {
        if (!this.props.item.image.data) {
          this.imgContainer.innerHTML = tex('graphic_drop_or_pick')
        } else {
          img.src = this.props.item.image.data
        }
      }
    }
  }

  componentWillUnmount() {
    window.removeEventListener('resize', this.onResize)
  }

  onDropImage(files) {
    if (files && files.length > 0) {
      this.onSelectImage(files[0])
    }
  }

  onSelectImage(file) {
    if (file.type.indexOf('image') !== 0) {
      return this.props.onChange(actions.selectImage({_type: file.type}))
    }

    if (file.size > MAX_IMG_SIZE) {
      return this.props.onChange(actions.selectImage({_size: file.size}))
    }

    const reader = new window.FileReader()
    reader.onload = e => {
      const img = this.createImage(e.target.result)
      img.onload = () => {
        this.props.onChange(actions.selectImage({
          type: file.type,
          data: e.target.result,
          width: img.naturalWidth,
          height: img.naturalHeight,
          _clientWidth: img.width,
          _clientHeight: img.height,
          _size: file.size
        }))
      }
    }
    reader.readAsDataURL(file)
  }

  createImage(encodedString, url) {
    const img = document.createElement('img')
    img.src = encodedString || asset(url)
    img.className = this.props.item._mode !== MODE_SELECT ? 'point-mode' : ''
    img.addEventListener('click', this.onClickImage)
    this.imgContainer.innerHTML = ''
    this.imgContainer.appendChild(img)

    return img
  }

  onClickImage(e) {
    if (this.props.item._mode !== MODE_SELECT) {
      e.stopPropagation()
      const imgRect = e.target.getBoundingClientRect()
      this.props.onChange(actions.createArea(
        e.clientX - imgRect.left,
        e.clientY - imgRect.top
      ))
    }
  }

  onResize() {
    const img = this.imgContainer.querySelector('img')

    if (img) {
      this.props.onChange(actions.resizeImage(img.width, img.height))
    }
  }

  getCurrentArea() {
    return this.props.item.solutions.find(
      solution => solution.area.id === this.props.item._popover.areaId
    )
  }

  getClientArea(area) {
    return Object.assign({}, area, area.shape === SHAPE_RECT ?
      {
        coords: area.coords.map(coords => ({
          x: coords._clientX,
          y: coords._clientY
        }))
      } :
      {
        radius: area._clientRadius,
        center: {
          x: area.center._clientX,
          y: area.center._clientY
        }
      }
    )
  }

  render() {
    return (
      <div className="graphic-editor">
        {get(this.props.item, '_errors.image') &&
          <ErrorBlock
            text={this.props.item._errors.image}
            warnOnly={!this.props.validating}
          />
        }
        <div className="top-controls">
          <ImageInput onSelect={file => this.onSelectImage(file)}/>
          <ModeSelector
            currentMode={this.props.item._mode}
            onChange={mode => this.props.onChange(actions.selectMode(mode))}
          />
        </div>
        {this.props.item._popover.open &&
          <AreaPopover
            left={this.props.item._popover.left}
            top={this.props.item._popover.top}
            score={this.getCurrentArea().score}
            feedback={this.getCurrentArea().feedback}
            color={this.getCurrentArea().area.color}
            onPickColor={color => this.props.onChange(
              actions.setAreaColor(this.props.item._popover.areaId, color)
            )}
            onChangeScore={score => this.props.onChange(
              actions.setSolutionProperty(this.props.item._popover.areaId, 'score', score)
            )}
            onChangeFeedback={feedback => this.props.onChange(
              actions.setSolutionProperty(this.props.item._popover.areaId, 'feedback', feedback)
            )}
            onClose={() => this.props.onChange(
              actions.togglePopover(this.props.item._popover.areaId, 0, 0, false)
            )}
          />
        }
        <FileDropZone onDrop={this.onDropImage}>
          <div className="img-dropzone">
            <div className="img-widget">
              <AnswerDropZone onDrop={(item, props, offset) => {
                if (item.type === TYPE_AREA_RESIZER) {
                  this.props.onChange(
                    actions.resizeArea(item.areaId, item.position, offset.x, offset.y)
                  )
                } else {
                  this.props.onChange(actions.moveArea(item.id, offset.x, offset.y))
                }
              }}>
                <div>
                  <div className="img-container" ref={el => this.imgContainer = el}/>
                  <ResizeDragLayer
                    canDrag={!this.props.item._popover.open}
                    areas={this.props.item.solutions.map(
                      solution => this.getClientArea(solution.area)
                    )}
                  />
                  {this.props.item.solutions.map(solution =>
                    <AnswerAreaDraggable
                      key={solution.area.id}
                      id={solution.area.id}
                      color={solution.area.color}
                      shape={solution.area.shape}
                      selected={this.props.item._mode === MODE_SELECT && solution._selected}
                      onSelect={id => this.props.onChange(actions.selectArea(id))}
                      onDelete={id => this.props.onChange(actions.deleteArea(id))}
                      canDrag={!this.props.item._popover.open
                        || this.props.item._popover.areaId !== solution.area.id}
                      togglePopover={(areaId, left, top) => {
                        const hasPopover = this.props.item._popover.open
                          && this.props.item._popover.areaId === solution.area.id
                        this.props.onChange(
                          actions.togglePopover(areaId, left, top, !hasPopover)
                        )
                      }}
                      geometry={this.getClientArea(solution.area)}
                    />
                  )}
                </div>
              </AnswerDropZone>
            </div>
          </div>
        </FileDropZone>
      </div>
    )
  }
}

Graphic.propTypes = {
  item: T.shape({
    image: T.oneOfType([
      T.shape({
        data: T.string.isRequired
      }),
      T.shape({
        url: T.string.isRequired
      })
    ]).isRequired,
    solutions: T.arrayOf(T.shape({
      area: T.shape({
        id: T.string.isRequired,
        shape: T.string.isRequired,
        color: T.string.isRequired
      }).isRequired
    })).isRequired,
    _mode: T.string.isRequired,
    _errors: T.object,
    _currentColor: T.string.isRequired,
    _popover: T.shape({
      areaId: T.string.isRequired,
      open: T.bool.isRequired,
      top: T.number.isRequired,
      left: T.number.isRequired
    }).isRequired
  }).isRequired,
  validating: T.bool.isRequired,
  onChange: T.func.isRequired
}
