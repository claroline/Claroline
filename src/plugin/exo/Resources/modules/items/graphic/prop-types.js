import {PropTypes as T} from 'prop-types'

import {makeId} from '#/main/core/scaffolding/id'

const GraphicItem = {
  propTypes: {
    image: T.oneOfType([
      T.shape({
        data: T.string.isRequired,
        _clientWidth: T.integer,
        _clientHeigth: T.integer
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
      }).isRequired,
      score: T.number,
      feedback: T.string
    })).isRequired,
    _currentColor: T.string,
    _popover: T.shape({
      areaId: T.string.isRequired,
      open: T.bool.isRequired,
      top: T.number.isRequired,
      left: T.number.isRequired
    }).isRequired
  },

  defaultProps: {
    image: {
      id: makeId(),
      type: '',
      data: '',
      width: 0,
      height: 0
    },
    pointers: 0,
    //required by the json schema altough not implemented
    pointerMode: 'pointer',
    _currentColor: '#000000',
    solutions: []
  }
}

export {
  GraphicItem
}
