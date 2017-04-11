import React from 'react'
import freeze from 'deep-freeze'
import merge from 'lodash/merge'
import {shallow, mount} from 'enzyme'
import assert from 'assert'
import {spyConsole, renew, ensure, mockTranslator} from '#/main/core/tests'
import {actions} from './../../quiz/editor/actions'
import definition from './index'
import {actions as subActions} from './editor'

describe('Words reducer', () => {
  const reduce = definition.editor.reduce

  it('augments and decorates base question on creation', () => {
    const item = {
      id: '1',
      type: 'application/x.words+json',
      content: 'Question?'
    }
    const reduced = reduce(item, actions.createItem('1', 'application/x.words+json'))

    // Check keywords _id are generated
    assert(!!reduced.solutions[0]._id, 'should generate _id for solutions')

    ensure.equal(reduced, {
      id: '1',
      type: 'application/x.words+json',
      content: 'Question?',
      solutions: [
        {
          _id: reduced.solutions[0]._id,
          text: '',
          caseSensitive: false,
          score: 1,
          feedback: '',
          _deletable: false
        }
      ],
      _wordsCaseSensitive: false
    })
  })

  it('updates base properties', () => {
    const item = makeFixture()
    const reduced = reduce(item, subActions.updateProperty('_wordsCaseSensitive', true))
    const expected = makeFixture({_wordsCaseSensitive: true})
    ensure.equal(reduced, expected)
  })

  it('sanitizes incoming solution data', () => {
    const item = makeFixture()
    const reduced = reduce(item, subActions.updateSolution(item.solutions[0]._id, 'score', '3'))
    const expected = makeFixture({solutions: [{score: 3}]})
    ensure.equal(reduced, expected)
  })

  it('updates solution text', () => {
    const item = makeFixture()
    const reduced = reduce(item, subActions.updateSolution(item.solutions[0]._id, 'text', 'This is new'))
    const expected = makeFixture({solutions: [{text: 'This is new'}]})
    ensure.equal(reduced, expected)
  })

  it('updates solution score', () => {
    const item = makeFixture()
    const reduced = reduce(item, subActions.updateSolution(item.solutions[0]._id, 'score', 3))
    const expected = makeFixture({solutions: [{score: 3}, {}]})
    ensure.equal(reduced, expected)
  })

  it('updates solution caseSensitive', () => {
    const item = makeFixture()
    const reduced = reduce(item, subActions.updateSolution(item.solutions[1]._id, 'caseSensitive', true))
    const expected = makeFixture({solutions: [{}, {caseSensitive: true}]})
    ensure.equal(reduced, expected)
  })

  it('updates solutions and deletable property on solution add', () => {
    const item = makeFixture()
    const reduced = reduce(item, subActions.addSolution())

    // Check keywords _id are generated
    assert(!!reduced.solutions[2]._id, 'should generate _id for solutions')

    const expected = makeFixture({
      solutions: [
        {},
        {},
        {
          _id: reduced.solutions[2]._id,
          text: '',
          feedback: '',
          score: 1,
          caseSensitive: false,
          _deletable: true
        }
      ]
    })
    ensure.equal(reduced, expected)
  })

  it('updates solutions on solution remove', () => {
    const item = makeFixture()
    const reduced = reduce(item, subActions.removeSolution(item.solutions[0]._id))
    const expected = makeFixture({}, false)
    expected.solutions.splice(0, 1)
    expected.solutions[0]._deletable = false
    ensure.equal(reduced, expected)
  })
})

// TODO : move keywords validation in its own test suite
describe('Words validator', () => {
  before(mockTranslator)
  const validate = definition.editor.validate
  it('checks solutions text are not empty', () => {
    const errors = validate({
      solutions: [
        {
          _id: '123',
          text: '',
          feedback: '',
          caseSensitive: false,
          score: 2
        }
      ]
    })
    ensure.equal(errors, {
      keywords: {text: 'words_empty_text_error'}
    })
  })

  it('checks solutions scores are not empty', () => {
    const errors = validate({
      solutions: [
        {
          _id: '123',
          text: 'lorem',
          feedback: '',
          caseSensitive: false,
          score: 2
        }, {
          _id: '123',
          text:'A',
          feedback:'',
          caseSensitive: false,
          score: ''
        }
      ]
    })
    ensure.equal(errors, {
      keywords: {score: 'words_score_not_valid'}
    })
  })

  it('checks solutions scores are valid numbers', () => {
    const errors = validate({
      solutions: [
        {
          _id: '123',
          text: 'lorem',
          feedback: '',
          caseSensitive: false,
          score: 2
        }, {
          _id: '123',
          text:'A',
          feedback:'',
          caseSensitive: false,
          score: []
        }
      ]
    })
    ensure.equal(errors, {
      keywords: {score: 'words_score_not_valid'}
    })
  })

  it('checks at least one solution has a score greater than 0', () => {
    const errors = validate({
      solutions: [
        {
          _id: '123',
          text:'A',
          feedback:'',
          caseSensitive: false,
          score: 0
        }
      ]
    })
    ensure.equal(errors, {
      keywords: {noValidKeyword: 'words_no_valid_solution'}
    })
  })

  it('returns no errors if item is valid', () => {
    const errors = validate({
      solutions: [
        {
          _id: '123',
          text:'A',
          feedback:'',
          caseSensitive: false,
          score: 2
        }
      ]
    })
    ensure.equal(errors, {})
  })
})

describe('<Words/>', () => {
  const Words = definition.editor.component

  beforeEach(() => {
    spyConsole.watch()
    renew(Words, 'Words')
  })
  afterEach(spyConsole.restore)

  it('has required props', () => {
    shallow(<Words item={{solutions: [], _wordsCaseSensitive: true}} />)
    ensure.missingProps('Words', ['item.id', 'onChange', 'validating'])
  })

  it('has typed props', () => {
    shallow(
      <Words
        item={
          {
            id: [],
            solutions: {},
            _wordsCaseSensitive: {}
          }
        }
        onChange={false}
      />
    )
    ensure.invalidProps('Words', ['item.id', 'onChange'])
  })

  it('renders appropriate fields and handle changes', () => {
    let updatedValue = null

    const item = {
      id: '1',
      content: 'Question?',
      solutions: [
        {
          _id: '789',
          text: 'A',
          feedback: '',
          caseSensitive: false,
          score: 2,
          _deletable: false
        }
      ],
      _wordsCaseSensitive: true
    }

    const form = mount(
      <Words
        item={item}
        validating={false}
        onChange={value => updatedValue = value}
      />
    )
    ensure.propTypesOk()

    const text = form.find('#keyword-'+item.solutions[0]._id+'-text')
    ensure.equal(text.length, 1, 'has text input')
    // @TODO find a way to test that changes are handled

    const score = form.find('#keyword-'+item.solutions[0]._id+'-score')
    ensure.equal(score.length, 1, 'has score input')
    score.simulate('change', {target: {value: 5}})
    ensure.equal(updatedValue.value, 5)
    ensure.equal(updatedValue.property, 'score')

    // @TODO find a way to test that the click action result in a new form-row
    const addBtn = form.find('.footer .btn')
    ensure.equal(addBtn.length, 1, 'has add button')
    addBtn.simulate('click')
  })
})


function makeFixture(props = {}, frozen = true) {
  const fixture = merge({
    id: '1',
    type: 'application/x.words+json',
    content: 'Question?',
    solutions: [
      {
        _id: '1234',
        text:'A',
        caseSensitive: false,
        score: 1,
        feedback: '',
        _deletable: true
      },
      {
        _id: '123456',
        text:'B',
        caseSensitive: true,
        score: 2,
        feedback: '',
        _deletable: true
      }
    ]
  }, props)

  return frozen ? freeze(fixture) : fixture
}
