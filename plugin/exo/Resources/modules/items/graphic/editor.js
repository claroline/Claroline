import {ITEM_CREATE} from './../../quiz/editor/actions'
import {makeId} from './../../utils/utils'
import {tex} from '#/main/core/translation'
import {resizeArea} from './resize'
import {
  MODE_RECT,
  MODE_SELECT,
  MAX_IMG_SIZE,
  SHAPE_RECT,
  SHAPE_CIRCLE,
  AREA_DEFAULT_SIZE
} from './enums'
import {
  SELECT_MODE,
  SELECT_IMAGE,
  RESIZE_IMAGE,
  CREATE_AREA,
  SELECT_AREA,
  MOVE_AREA,
  DELETE_AREA,
  TOGGLE_POPOVER,
  SET_AREA_COLOR,
  SET_SOLUTION_PROPERTY,
  RESIZE_AREA,
  BLUR_AREAS
} from './actions'
import {Graphic as component} from './editor.jsx'

function reduce(item = {}, action = {}) {
  switch (action.type) {
    case ITEM_CREATE:
      return decorate(Object.assign({}, item, {
        image: blankImage(),
        pointers: 0,
        //required by the json schema altough not implemented
        pointerMode: 'pointer',
        solutions: []
      }))
    case SELECT_MODE:
      return Object.assign({}, item, {
        _mode: action.mode,
        _popover: Object.assign({}, item._popover, {open: false})
      })
    case SELECT_IMAGE:
      return Object.assign({}, item, {
        image: Object.assign(
          blankImage(),
          {id: item.image.id},
          action.image
        ),
        solutions: [],
        pointers: 0,
        _popover: Object.assign({}, item._popover, {open: false})
      })
    case RESIZE_IMAGE: {
      const sizeRatio = item.image.width / action.width
      const toClient = length => Math.round(length / sizeRatio)

      return Object.assign({}, item, {
        image: Object.assign({}, item.image, {
          _clientWidth: action.width,
          _clientHeight: action.height
        }),
        solutions: item.solutions.map(solution => {
          if (solution.area.shape === SHAPE_RECT) {
            return Object.assign({}, solution, {
              area: Object.assign({}, solution.area, {
                coords: solution.area.coords.map(coords => Object.assign({}, coords, {
                  _clientX: toClient(coords.x),
                  _clientY: toClient(coords.y)
                }))
              })
            })
          } else {
            return Object.assign({}, solution, {
              area: Object.assign({}, solution.area, {
                center: Object.assign({}, solution.area.center, {
                  _clientX: toClient(solution.area.center.x),
                  _clientY: toClient(solution.area.center.y)
                }),
                _clientRadius: toClient(solution.area.radius)
              })
            })
          }
        }),
        _popover: Object.assign({}, item._popover, {open: false})
      })
    }
    case CREATE_AREA: {
      const clientX = action.x
      const clientY = action.y
      const clientHalfSize = AREA_DEFAULT_SIZE / 2
      const absX = toAbs(clientX, item.image)
      const absY = toAbs(clientY, item.image)
      const absHalfSize = toAbs(clientHalfSize, item.image)
      const area = {
        id: makeId(),
        shape: item._mode === MODE_RECT ? SHAPE_RECT : SHAPE_CIRCLE,
        color: item._currentColor
      }

      if (area.shape === SHAPE_CIRCLE) {
        area.center = {
          x: absX,
          y: absY,
          _clientX: clientX,
          _clientY: clientY
        }
        area.radius = absHalfSize
        area._clientRadius = clientHalfSize
      } else {
        area.coords = [
          {
            x: absX - absHalfSize,
            y: absY - absHalfSize,
            _clientX: clientX - clientHalfSize,
            _clientY: clientY - clientHalfSize
          },
          {
            x: absX + absHalfSize,
            y: absY + absHalfSize,
            _clientX: clientX + clientHalfSize,
            _clientY: clientY + clientHalfSize
          }
        ]
      }

      return Object.assign({}, item, {
        pointers: item.pointers + 1,
        solutions: [
          ...item.solutions.map(solution => Object.assign({}, solution, {
            _selected: false
          })),
          {
            score: 1,
            feedback: '',
            _selected: true,
            area
          }
        ],
        _mode: MODE_SELECT,
        _popover: Object.assign({}, item._popover, {open: false})
      })
    }
    case SELECT_AREA:
      return Object.assign({}, item, {
        solutions: item.solutions.map(solution => Object.assign({}, solution, {
          _selected: solution.area.id === action.id
        })),
        _mode: MODE_SELECT,
        _popover: Object.assign({}, item._popover, {
          open: item._popover.open && item._popover.areaId === action.id
        })
      })
    case MOVE_AREA:
      return Object.assign({}, item, {
        solutions: item.solutions.map(solution => {
          if (solution.area.id === action.id) {
            // action coordinates are the offset resulting from the move
            if (solution.area.shape === SHAPE_CIRCLE) {
              return Object.assign({}, solution, {
                area: Object.assign({}, solution.area, {
                  center: {
                    x: solution.area.center.x + toAbs(action.x, item.image),
                    y: solution.area.center.y + toAbs(action.y, item.image),
                    _clientX: solution.area.center._clientX + action.x,
                    _clientY: solution.area.center._clientY + action.y
                  }
                })
              })
            } else {
              return Object.assign({}, solution, {
                area: Object.assign({}, solution.area, {
                  coords: solution.area.coords.map(coords => ({
                    x: coords.x + toAbs(action.x, item.image),
                    y: coords.y + toAbs(action.y, item.image),
                    _clientX: coords._clientX + action.x,
                    _clientY: coords._clientY + action.y
                  }))
                })
              })
            }
          }
          return solution
        }),
        _popover: Object.assign({}, item._popover, {open: false})
      })
    case DELETE_AREA:
      return Object.assign({}, item, {
        solutions: item.solutions.filter(
          solution => solution.area.id !== action.id
        ),
        _popover: Object.assign({}, item._popover, {open: false})
      })
    case TOGGLE_POPOVER:
      return Object.assign({}, item, {
        _popover: {
          areaId: action.areaId,
          open: action.open,
          left: action.left,
          top: action.top
        }
      })
    case SET_AREA_COLOR:
      return Object.assign({}, item, {
        solutions: item.solutions.map(solution => {
          if (solution.area.id === action.id) {
            return Object.assign({}, solution, {
              area: Object.assign({}, solution.area, {
                color: action.color
              })
            })
          }
          return solution
        }),
        _currentColor: action.color
      })
    case SET_SOLUTION_PROPERTY:
      return Object.assign({}, item, {
        solutions: item.solutions.map(solution => {
          if (solution.area.id === action.id) {
            return Object.assign({}, solution, {
              [action.property]: action.value
            })
          }
          return solution
        })
      })
    case RESIZE_AREA:
      return Object.assign({}, item, {
        solutions: item.solutions.map(solution => {
          if (solution.area.id === action.id) {
            const area = resizeArea(
              getClientArea(solution.area),
              action.position,
              action.x,
              action.y
            )
            if (solution.area.shape === SHAPE_CIRCLE) {
              return Object.assign({}, solution, {
                area: Object.assign({}, solution.area, {
                  center: {
                    x: toAbs(area.center.x, item.image),
                    y: toAbs(area.center.y, item.image),
                    _clientX: area.center.x,
                    _clientY: area.center.y
                  },
                  radius: toAbs(area.radius, item.image),
                  _clientRadius: area.radius
                })
              })
            } else {
              return Object.assign({}, solution, {
                area: Object.assign({}, solution.area, {
                  coords: solution.area.coords.map((coords, index) => ({
                    x: toAbs(area.coords[index].x, item.image),
                    y: toAbs(area.coords[index].y, item.image),
                    _clientX: area.coords[index].x,
                    _clientY: area.coords[index].y
                  }))
                })
              })
            }
          }
          return solution
        })
      })
    case BLUR_AREAS:
      return Object.assign({}, item, {
        solutions: item.solutions.map(
          solution => Object.assign({}, solution, {_selected: false})
        ),
        _popover: Object.assign({}, item._popover, {open: false})
      })
  }
  return item
}

function toAbs(length, imgProps) {
  const sizeRatio = imgProps.width / imgProps._clientWidth
  return Math.round(length * sizeRatio)
}

function blankImage() {
  return {
    id: makeId(),
    type: '',
    data: '',
    width: 0,
    height: 0
  }
}

function getClientArea(area) {
  return area.shape === SHAPE_RECT ?
    {
      shape: area.shape,
      coords: area.coords.map(coords => ({
        x: coords._clientX,
        y: coords._clientY
      }))
    } :
    {
      shape: area.shape,
      radius: area._clientRadius,
      center: {
        x: area.center._clientX,
        y: area.center._clientY
      }
    }
}

function decorate(item) {
  return Object.assign({}, item, {
    image: Object.assign({}, item.image, {
      _clientWidth: 0,
      _clientHeight: 0
    }),
    solutions: item.solutions.map(solution => {
      if (solution.area.shape === SHAPE_RECT) {
        return Object.assign({}, solution, {
          area: Object.assign({}, solution.area, {
            coords: solution.area.coords.map(coords => Object.assign({}, coords, {
              // we don't know the real values until image has been loaded into the dom
              _clientX: 0,
              _clientY: 0
            }))
          }),
          _selected: false
        })
      } else {
        return Object.assign({}, solution, {
          area: Object.assign({}, solution.area, {
            center: Object.assign({}, solution.area.center, {
              _clientX: 0,
              _clientY: 0
            }),
            _clientRadius: 0
          }),
          _selected: false
        })
      }
    }),
    _mode: MODE_RECT,
    _currentColor: '#0693e3',
    _popover: {
      areaId: '',
      open: false,
      top: 0,
      left: 0
    }
  })
}

function validate(item) {
  if (item.image.type && item.image.type.indexOf('image') !== 0) {
    return {image: tex('graphic_error_not_an_image')}
  }

  if (item.image._size && item.image._size > MAX_IMG_SIZE) {
    return {image: tex('graphic_error_image_too_large')}
  }

  if (!item.image.data && !item.image.url) {
    return {image: tex('graphic_error_no_image')}
  }

  if (item.solutions.length === 0) {
    return {image: tex('graphic_error_no_solution')}
  }

  if (!item.solutions.find(solution => solution.score > 0)) {
    return {image: tex('graphic_error_no_positive_solution')}
  }

  return {}
}

export default {
  component,
  reduce,
  decorate,
  validate
}
