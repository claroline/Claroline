import React from 'react'
import freeze from 'deep-freeze'
import merge from 'lodash/merge'
import {shallow, mount} from 'enzyme'
import {spyConsole, renew, ensure, mockTranslator} from './../../utils/test'
import {lastId, lastIds} from './../../utils/utils'
import {actions} from './../../quiz/editor/actions'
import {actions as subActions} from './editor'
import definition from './index'

describe('Match reducer', () => {
  const reduce = definition.editor.reduce

  it('augments and decorates base question on creation', () => {
    const item = {
      id: '1',
      type: 'application/x.match+json',
      content: 'Question?'
    }
    const reduced = reduce(item, actions.createItem('1', 'application/x.match+json'))
    const ids = lastIds(3)
    ensure.equal(reduced, {
      id: '1',
      type: 'application/x.match+json',
      content: 'Question?',
      random: false,
      penalty: 0,
      firstSet: [
        {
          id: ids[0],
          type: 'text/html',
          data: '',
          _deletable: false
        }
      ],
      secondSet: [
        {
          id: ids[1],
          type: 'text/html',
          data: '',
          _deletable: false
        },
        {
          id:ids[2],
          type: 'text/html',
          data: '',
          _deletable: false
        }
      ],
      solutions: []
    })
  })

  it('updates random base propertie and marks it as touched', () => {
    const item = makeFixture()
    const reduced = reduce(item, subActions.updateProperty('random', true))
    const expected = makeFixture({random: true, _touched: {random: true}})
    ensure.equal(reduced, expected)
  })

  it('updates penalty base propertie and marks it as touched', () => {
    const item = makeFixture()
    const reduced = reduce(item, subActions.updateProperty('penalty', 1))
    const expected = makeFixture({penalty: 1, _touched: {penalty: true}})
    ensure.equal(reduced, expected)
  })

  it('sanitizes incoming data', () => {
    const item = makeFixture()
    const reduced = reduce(item, subActions.updateProperty('penalty', '1'))
    const expected = makeFixture({penalty: 1, _touched: {penalty: true}})
    ensure.equal(reduced, expected)
  })

  it('updates solution data', () => {
    const item = makeFixture()
    const reduced = reduce(item, subActions.updateSolution('1', '1', 'data', 'Data updated'))
    const expected = makeFixture({solutions: [{data: 'Data updated'}], _touched: {data: true}})
    ensure.equal(reduced, expected)
  })

  it('updates solution score', () => {
    const item = makeFixture()
    const reduced = reduce(item, subActions.updateSolution('1', '1', 'score', -1))
    const expected = makeFixture({solutions: [{score: -1}], _touched: {score: true}})
    ensure.equal(reduced, expected)
  })

  it('adds an item to firstSet when asked', () => {
    const item = makeFixture()
    const reduced = reduce(item, subActions.addItem(true))
    const expected = makeFixture({
      firstSet: [{}, {},
        {
          id: lastId(),
          type: 'text/html',
          data: '',
          _deletable: true
        }
      ],
      secondSet: [{}, {}]
    })
    ensure.equal(reduced, expected)
  })

  it('adds an item to secondSet when asked', () => {
    const item = makeFixture()
    const reduced = reduce(item, subActions.addItem(false))
    const expected = makeFixture({
      firstSet: [{}, {}],
      secondSet: [{}, {},
        {
          id: lastId(),
          type: 'text/html',
          data: '',
          _deletable: true
        }
      ]
    })
    ensure.equal(reduced, expected)
  })

  it('removes an item from firstSet when asked, update items _deletable property and solutions', () => {
    const item = makeFixture()
    const reduced = reduce(item, subActions.removeItem(true, '1'))
    const expected = makeFixture({}, false)
    expected.firstSet.splice(0, 1)
    expected.firstSet.forEach(set => set._deletable = false)
    expected.secondSet.forEach(set => set._deletable = false)
    expected.solutions.splice(0, 2)
    ensure.equal(reduced, expected)
  })

  it('removes an item from secondSet when asked, update items _deletable property and solutions', () => {
    const item = makeFixture()
    const reduced = reduce(item, subActions.removeItem(false, '1'))
    const expected = makeFixture({}, false)
    expected.secondSet.splice(0, 1)
    expected.firstSet.forEach(set => set._deletable = false)
    expected.secondSet.forEach(set => set._deletable = false)
    expected.solutions.splice(0, 1)
    ensure.equal(reduced, expected)
  })

  it('updates solution feedback', () => {
    const item = makeFixture()
    const reduced = reduce(item, subActions.updateSolution('1', '1', 'feedback', 'This is new'))
    const expected = makeFixture({solutions: [{feedback: 'This is new'}], _touched:{feedback :true}})
    ensure.equal(reduced, expected)
  })

  it('updates solution score', () => {
    const item = makeFixture()
    const reduced = reduce(item, subActions.updateSolution('1', '1', 'score', 3))
    const expected = makeFixture({solutions: [{score: 3}], _touched:{score :true}})
    ensure.equal(reduced, expected)
  })

  it('updates solutions on solution add', () => {
    const item = makeFixture()
    const solution = {
      firstId: '2',
      secondId: '2',
      feedback: '',
      score: 1
    }
    const reduced = reduce(item, subActions.addSolution(solution))
    const expected = makeFixture({
      solutions: [{},{},
        {
          firstId: '2',
          secondId: '2',
          feedback: '',
          score: 1,
          _deletable: true
        }
      ]
    })
    ensure.equal(reduced, expected)
  })

  it('updates solutions on solution removed', () => {
    const item = makeFixture()
    const reduced = reduce(item, subActions.removeSolution('1','2'))
    const expected = makeFixture({}, false)
    expected.solutions.splice(1,1)
    expected.solutions.forEach(solution => solution._deletable = false)
    ensure.equal(reduced, expected)
  })

})

describe('Match validator', () => {
  before(mockTranslator)
  const validate = definition.editor.validate

  it('checks match penalty validity', () => {
    const item = makeFixture({
      penalty: 'ABC'
    })
    const errors = validate(item)
    ensure.equal(errors, {
      items: 'match_penalty_not_valid'
    })
  })

  it('checks items data is not empty', () => {
    const item = makeFixture({
      firstSet: [{}, {}],
      secondSet: [{}, {data:''}]
    })
    const errors = validate(item)
    ensure.equal(errors, {
      items: 'match_item_empty_data_error'
    })
  })

  it('warns user if there is a joint use of negative score and penalty', () => {
    const item = makeFixture({
      penalty: 1,
      solutions: [
        {},
        {score: -1}
      ]
    })
    const errors = validate(item)
    ensure.equal(errors, {
      warning: 'match_warning_penalty_and_negative_scores'
    })
  })

  it('checks that at least one solution exists', () => {
    const item = makeFixture({}, false)
    item.solutions.splice(0, 2)
    const errors = validate(item)
    ensure.equal(errors, {
      solutions: 'match_no_solution'
    })
  })

  it('checks that at least one solution with a score that is greater than 0', () => {
    const item = makeFixture({
      solutions: [
        {score: -2},
        {score: -1}
      ]
    })
    const errors = validate(item)
    ensure.equal(errors, {
      solutions: 'match_no_valid_solution'
    })
  })

  it('checks that each solution should have a valid score', () => {
    const item = makeFixture({
      solutions: [
        {score: 'abc'},
        {}
      ]
    })
    const errors = validate(item)
    ensure.equal(errors, {
      solutions: 'match_score_not_valid'
    })
  })

  it('returns no errors if item is valid', () => {
    const item = makeFixture()
    const errors = validate(item)
    ensure.equal(errors, {})
  })
})



describe('<Match />', () => {
  window.jsPlumb = {
    getInstance: () => {
      return {
        getSelector: () => {},
        addEndpoint: () => {},
        setSuspendDrawing: () => {},
        importDefaults: () => {},
        registerConnectionTypes: () => {},
        connect:() => {},
        setContainer: () => {},
        bind: () => {},
        getConnections: () => {},
        removeAllEndpoints: () => {},
        detach: () => {},
        repaintEverything: () => {}
      }
    }
  }

  const Match = definition.editor.component

  beforeEach(() => {
    spyConsole.watch()
    renew(Match, 'Match')
  })
  afterEach(spyConsole.restore)

  it('has required props', () => {
    shallow(<Match item={{foo:'baz', firstSet:[], secondSet:[], solutions:[]}}/>)
    ensure.missingProps('Match', ['validating', 'onChange', 'item.id'])
  })

  it('has typed props', () => {
    shallow(
      <Match
        item={
          {
            id: [],
            penalty: 'a',
            random: [],
            firstSet:[],
            secondSet: [],
            solutions: [],
            _errors: {}
          }
        }
        onChange={false}
      />
    )
    ensure.invalidProps('Match', ['item.id', 'onChange'])
  })

  it('renders appropriate fields and handle changes', () => {

    document.getElementById = () => {}
    window.jsPlumb = {
      getInstance: () => {
        return {
          getSelector: () => {},
          addEndpoint: () => {},
          setSuspendDrawing: () => {},
          importDefaults: () => {},
          registerConnectionTypes: () => {},
          connect:() => {},
          setContainer: () => {},
          bind: () => {},
          getConnections: () => {},
          removeAllEndpoints: () => {},
          detach: () => {},
          repaintEverything: () => {}
        }
      }
    }

    mount(
      <Match
        item={{
          id: '1',
          type: 'application/x.match+json',
          content: 'Question?',
          random: false,
          penalty: 0,
          firstSet: [
            {
              id: '1',
              type: 'text/html',
              data: 'A',
              _deletable: true
            },
            {
              id: '2',
              type: 'text/html',
              data: 'B',
              _deletable: true
            }
          ],
          secondSet: [
            {
              id: '1',
              type: 'text/html',
              data: 'C',
              _deletable: true
            },
            {
              id:'2',
              type: 'text/html',
              data: 'D',
              _deletable: true
            }
          ],
          solutions: [
            {
              firstId: '1',
              secondId: '1',
              score: 2,
              feedback: 'Well done',
              _deletable: true
            },
            {
              firstId: '1',
              secondId: '2',
              score: 1,
              feedback: 'Congrats',
              _deletable: true
            }
          ]
        }}
        validating={false}
        onChange={() => {}}
      />
    )
    ensure.propTypesOk()
  })
})


function makeFixture(props = {}, frozen = true) {
  const fixture = merge({
    id: '1',
    type: 'application/x.match+json',
    content: 'Question?',
    random: false,
    penalty: 0,
    firstSet: [
      {
        id: '1',
        type: 'text/html',
        data: 'A',
        _deletable: true
      },
      {
        id: '2',
        type: 'text/html',
        data: 'B',
        _deletable: true
      }
    ],
    secondSet: [
      {
        id: '1',
        type: 'text/html',
        data: 'C',
        _deletable: true
      },
      {
        id:'2',
        type: 'text/html',
        data: 'D',
        _deletable: true
      }
    ],
    solutions: [
      {
        firstId: '1',
        secondId: '1',
        score: 2,
        feedback: 'Well done',
        _deletable: true
      },
      {
        firstId: '1',
        secondId: '2',
        score: 1,
        feedback: 'Congrats',
        _deletable: true
      }
    ]
  }, props)

  return frozen ? freeze(fixture) : fixture
}
