import React from 'react'
import {shallow} from 'enzyme'
import freeze from 'deep-freeze'
import merge from 'lodash/merge'
import {spyConsole, renew, ensure} from '#/main/core/tests'
import {lastId} from './../../utils/utils'
import {actions} from './../../quiz/editor/actions'
import {actions as subActions} from './actions'
import {
  MODE_RECT,
  MODE_CIRCLE,
  MODE_SELECT,
  SHAPE_RECT,
  SHAPE_CIRCLE,
  AREA_DEFAULT_SIZE,
  DIR_SW,
  DIR_N
} from './enums'
import editor from './editor'
import {Graphic} from './editor.jsx'

describe('Graphic reducer', () => {
  it('creates a default item and hidden editor props on creation', () => {
    const item = freeze({
      id: 'ID-ITEM',
      type: 'application/x.graphic+json',
      content: 'Question?'
    })
    const reduced = editor.reduce(
      item,
      actions.createItem('1', 'application/x.graphic+json')
    )
    ensure.equal(reduced, itemFixture({
      image: {
        id: lastId()
      },
      _currentColor: '#0693e3',
      pointerMode: 'pointer'
    }))
  })

  it('updates the editor mode', () => {
    const item = itemFixture()
    const reduced = editor.reduce(item, subActions.selectMode(MODE_CIRCLE))
    ensure.equal(reduced, itemFixture({
      _mode: MODE_CIRCLE
    }))
  })

  it('updates the image on selection and removes previous pointers', () => {
    const item = itemFixture({
      solutions: [
        {
          area: {
            shape: SHAPE_CIRCLE,
            center: {x: 120, y: 150},
            radius: 40
          },
          score: 12
        }
      ],
      pointers: 1
    })
    const reduced = editor.reduce(item, subActions.selectImage({
      type: 'image/jpeg',
      url: 'foo',
      width: 200,
      height: 100,
      _clientWidth: 100,
      _clientHeight: 50
    }))
    ensure.equal(reduced, itemFixture({
      image: {
        type: 'image/jpeg',
        url: 'foo',
        width: 200,
        height: 100,
        _clientWidth: 100,
        _clientHeight: 50
      },
      solutions: [],
      pointers: 0
    }))
  })

  it('creates rectangular areas (scaled)', () => {
    const item = itemFixture({
      image: {
        id: 'ID',
        type: 'image/png',
        url: 'foo',
        width: 200,
        height: 200,
        _clientWidth: 100, // "real" image is twice as big
        _clientHeight: 100
      }
    })
    const reduced = editor.reduce(item, subActions.createArea(30, 10))
    ensure.equal(reduced, itemFixture({
      image: {
        id: 'ID',
        type: 'image/png',
        url: 'foo',
        width: 200,
        height: 200,
        _clientWidth: 100,
        _clientHeight: 100
      },
      pointers: 1,
      solutions: [
        {
          area: {
            id: lastId(),
            shape: SHAPE_RECT,
            coords: [
              {
                x: 60 - (AREA_DEFAULT_SIZE / 2) * 2,
                y: 20 - (AREA_DEFAULT_SIZE / 2) * 2,
                _clientX: 30 - (AREA_DEFAULT_SIZE / 2),
                _clientY: 10 - (AREA_DEFAULT_SIZE / 2)
              },
              {
                x: 60 + (AREA_DEFAULT_SIZE / 2) * 2,
                y: 20 + (AREA_DEFAULT_SIZE / 2) * 2,
                _clientX: 30 + (AREA_DEFAULT_SIZE / 2),
                _clientY: 10 + (AREA_DEFAULT_SIZE / 2)
              }
            ],
            color: '#00f'
          },
          score: 1,
          feedback: '',
          _selected: true
        }
      ],
      _mode: MODE_SELECT
    }))
  })

  it('creates circular areas (scaled)', () => {
    const item = itemFixture({
      image: {
        id: 'ID',
        type: 'image/png',
        url: 'foo',
        width: 200,
        height: 200,
        _clientWidth: 100,
        _clientHeight: 100
      },
      _mode: MODE_CIRCLE
    })
    const reduced = editor.reduce(item, subActions.createArea(50, 40))
    ensure.equal(reduced, itemFixture({
      image: {
        id: 'ID',
        type: 'image/png',
        url: 'foo',
        width: 200,
        height: 200,
        _clientWidth: 100, // "real" image is twice as big
        _clientHeight: 100
      },
      pointers: 1,
      solutions: [
        {
          area: {
            id: lastId(),
            shape: SHAPE_CIRCLE,
            center: {
              x: 100,
              y: 80,
              _clientX: 50,
              _clientY: 40
            },
            radius: (AREA_DEFAULT_SIZE / 2) * 2,
            _clientRadius: AREA_DEFAULT_SIZE / 2,
            color: '#00f'
          },
          score: 1,
          feedback: '',
          _selected: true
        }
      ],
      _mode: MODE_SELECT
    }))
  })

  it('selects existing areas and switches to select mode', () => {
    const item = itemFixture({
      solutions: [
        {
          area: {
            id: 'ID1',
            shape: SHAPE_RECT
          },
          _selected: false
        },
        {
          area: {
            id: 'ID2',
            shape: SHAPE_CIRCLE
          },
          _selected: false
        }
      ]
    })
    const reduced = editor.reduce(item, subActions.selectArea('ID2'))
    ensure.equal(reduced, itemFixture({
      solutions: [
        {
          area: {
            id: 'ID1',
            shape: SHAPE_RECT
          },
          _selected: false
        },
        {
          area: {
            id: 'ID2',
            shape: SHAPE_CIRCLE
          },
          _selected: true
        }
      ],
      _mode: MODE_SELECT
    }))
  })

  it('moves circular areas (scaled)', () => {
    const item = itemFixture({
      image: {
        width: 200,
        height: 200,
        _clientWidth: 100,
        _clientHeight: 100
      },
      solutions: [
        {
          area: {
            id: 'ID1',
            shape: SHAPE_CIRCLE,
            center: {
              x: 100,
              y: 100,
              _clientX: 50,
              _clientY: 50
            },
            radius: 20,
            _clientRadius: 10
          },
          _selected: true
        },
        {
          area: {
            id: 'ID2',
            shape: SHAPE_CIRCLE
          }
        }
      ]
    })
    const reduced = editor.reduce(item, subActions.moveArea('ID1', 20, 30))
    ensure.equal(reduced, itemFixture({
      image: {
        width: 200,
        height: 200,
        _clientWidth: 100,
        _clientHeight: 100
      },
      solutions: [
        {
          area: {
            id: 'ID1',
            shape: SHAPE_CIRCLE,
            center: {
              x: 140,
              y: 160,
              _clientX: 70,
              _clientY: 80
            },
            radius: 20,
            _clientRadius: 10
          },
          _selected: true
        },
        {
          area: {
            id: 'ID2',
            shape: SHAPE_CIRCLE
          }
        }
      ]
    }))
  })

  it('moves rectangular areas (scaled)', () => {
    const item = itemFixture({
      image: {
        width: 200,
        height: 200,
        _clientWidth: 100,
        _clientHeight: 100
      },
      solutions: [
        {
          area: {
            id: 'ID1',
            shape: SHAPE_RECT,
            coords: [
              {
                x: 50,
                y: 50,
                _clientX: 25,
                _clientY: 25
              },
              {
                x: 150,
                y: 150,
                _clientX: 75,
                _clientY: 75
              }
            ]
          },
          _selected: true
        },
        {
          area: {
            id: 'ID2',
            shape: SHAPE_CIRCLE
          }
        }
      ]
    })
    const reduced = editor.reduce(item, subActions.moveArea('ID1', -15, 5))
    ensure.equal(reduced, itemFixture({
      image: {
        width: 200,
        height: 200,
        _clientWidth: 100,
        _clientHeight: 100
      },
      solutions: [
        {
          area: {
            id: 'ID1',
            shape: SHAPE_RECT,
            coords: [
              {
                x: 20,
                y: 60,
                _clientX: 10,
                _clientY: 30
              },
              {
                x: 120,
                y: 160,
                _clientX: 60,
                _clientY: 80
              }
            ]
          },
          _selected: true
        },
        {
          area: {
            id: 'ID2',
            shape: SHAPE_CIRCLE
          }
        }
      ]
    }))
  })

  it('deletes areas', () => {
    const item = itemFixture({
      solutions: [
        {
          area: {
            id: 'ID1',
            shape: SHAPE_RECT
          }
        },
        {
          area: {
            id: 'ID2',
            shape: SHAPE_CIRCLE
          }
        }
      ]
    })
    const reduced = editor.reduce(item, subActions.deleteArea('ID2'))
    ensure.equal(reduced, itemFixture({
      solutions: [
        {
          area: {
            id: 'ID1',
            shape: SHAPE_RECT
          }
        }
      ]
    }))
  })

  it('toggles area popover', () => {
    const item = itemFixture({
      _popover: {
        areaId: 'ID1',
        open: false,
        top: 40,
        left: 50
      }
    })
    const reduced = editor.reduce(item,
      subActions.togglePopover('ID2', 70, 10, true)
    )
    ensure.equal(reduced, itemFixture({
      _popover: {
        areaId: 'ID2',
        open: true,
        top: 10,
        left: 70
      }
    }))
  })

  it('sets area color and current color', () => {
    const item = itemFixture({
      solutions: [
        {
          area: {
            id: 'ID1',
            shape: SHAPE_RECT,
            color: 'blue'
          }
        },
        {
          area: {
            id: 'ID2',
            shape: SHAPE_CIRCLE,
            color: 'red'
          }
        }
      ]
    })
    const reduced = editor.reduce(item, subActions.setAreaColor('ID2', 'yellow'))
    ensure.equal(reduced, itemFixture({
      solutions: [
        {
          area: {
            id: 'ID1',
            shape: SHAPE_RECT,
            color: 'blue'
          }
        },
        {
          area: {
            id: 'ID2',
            shape: SHAPE_CIRCLE,
            color: 'yellow'
          }
        }
      ],
      _currentColor: 'yellow'
    }))
  })

  it('sets solution properties', () => {
    const item = itemFixture({
      solutions: [
        {
          area: {
            id: 'ID1',
            shape: SHAPE_RECT
          }
        }
      ]
    })
    const reduced = editor.reduce(
      item,
      subActions.setSolutionProperty('ID1', 'foo', 'bar')
    )
    ensure.equal(reduced, itemFixture({
      solutions: [
        {
          area: {
            id: 'ID1',
            shape: SHAPE_RECT
          },
          foo: 'bar'
        }
      ]
    }))
  })

  it('resizes rectangular areas (scale)', () => {
    const item = itemFixture({
      image: {
        width: 200,
        height: 200,
        _clientWidth: 100,
        _clientHeight: 100
      },
      solutions: [
        {
          area: {
            id: 'ID1',
            shape: SHAPE_RECT,
            coords: [
              {
                x: 50,
                y: 50,
                _clientX: 25,
                _clientY: 25
              },
              {
                x: 150,
                y: 150,
                _clientX: 75,
                _clientY: 75
              }
            ]
          },
          _selected: true
        },
        {
          area: {
            id: 'ID2',
            shape: SHAPE_CIRCLE
          }
        }
      ]
    })
    const reduced = editor.reduce(item, subActions.resizeArea('ID1', DIR_SW, -15, 5))
    ensure.equal(reduced, itemFixture({
      image: {
        width: 200,
        height: 200,
        _clientWidth: 100,
        _clientHeight: 100
      },
      solutions: [
        {
          area: {
            id: 'ID1',
            shape: SHAPE_RECT,
            coords: [
              {
                x: 20,
                y: 50,
                _clientX: 10,
                _clientY: 25
              },
              {
                x: 150,
                y: 160,
                _clientX: 75,
                _clientY: 80
              }
            ]
          },
          _selected: true
        },
        {
          area: {
            id: 'ID2',
            shape: SHAPE_CIRCLE
          }
        }
      ]
    }))
  })

  it('resizes circular areas (scale)', () => {
    const item = itemFixture({
      image: {
        width: 200,
        height: 200,
        _clientWidth: 400,
        _clientHeight: 400
      },
      solutions: [
        {
          area: {
            id: 'ID1',
            shape: SHAPE_RECT
          }
        },
        {
          area: {
            id: 'ID2',
            shape: SHAPE_CIRCLE,
            center: {
              x: 15,
              y: 90,
              _clientX: 30,
              _clientY: 180
            },
            radius: 25,
            _clientRadius: 50
          },
          _selected: true
        }
      ]
    })
    const reduced = editor.reduce(item, subActions.resizeArea('ID2', DIR_N, 20, 40))
    ensure.equal(reduced, itemFixture({
      image: {
        width: 200,
        height: 200,
        _clientWidth: 400,
        _clientHeight: 400
      },
      solutions: [
        {
          area: {
            id: 'ID1',
            shape: SHAPE_RECT
          }
        },
        {
          area: {
            id: 'ID2',
            shape: SHAPE_CIRCLE,
            center: {
              x: 15,
              y: 90,
              _clientX: 30,
              _clientY: 180
            },
            radius: 5,
            _clientRadius: 10
          },
          _selected: true
        }
      ]
    }))
  })

  it('it blurs selected areas and closes popovers on outside clicks', () => {
    const item = itemFixture({
      solutions: [
        {
          area: {
            id: 'ID1'
          }
        },
        {
          area: {
            id: 'ID2'
          },
          _selected: true
        }
      ],
      _popover: {
        areaId: '2',
        open: true
      }
    })
    const reduced = editor.reduce(item, subActions.blurAreas())
    ensure.equal(reduced, itemFixture({
      solutions: [
        {
          area: {
            id: 'ID1'
          },
          _selected: false
        },
        {
          area: {
            id: 'ID2'
          },
          _selected: false
        }
      ],
      _popover: {
        areaId: '2',
        open: false
      }
    }))
  })
})

describe('<Graphic/>', () => {
  beforeEach(() => {
    spyConsole.watch()
    renew(Graphic, 'Graphic')
  })
  afterEach(spyConsole.restore)

  it('renders an empty image zone by default', () => {
    const graphic = shallow(
      <Graphic
        item={itemFixture()}
        validating={false}
        onChange={() => {}}
      />
    )
    ensure.propTypesOk()
    ensure.equal(graphic.find('.img-dropzone').length, 1)
    ensure.equal(graphic.find('.img-dropzone img').length, 0)
  })
})

function itemFixture(props = {}) {
  return freeze(merge({
    id: 'ID-ITEM',
    type: 'application/x.graphic+json',
    content: 'Question?',
    image: {
      id: 'ID-IMG',
      type: '',
      data: '',
      width: 0,
      height: 0,
      _clientWidth: 0,
      _clientHeight: 0
    },
    pointers: 0,
    solutions: [],
    _mode: MODE_RECT,
    _popover: {
      areaId: '',
      open: false,
      left: 0,
      top: 0
    },
    _currentColor: '#00f'
  }, props))
}
