import React from 'react'
import freeze from 'deep-freeze'
import merge from 'lodash/merge'
import {shallow, mount} from 'enzyme'
import {spyConsole, renew, ensure, mockTranslator} from './../../utils/test'
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
    ensure.equal(reduced, {
      id: '1',
      type: 'application/x.words+json',
      content: 'Question?',
      solutions: [
        {
          text:'',
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
    const reduced = reduce(item, subActions.updateSolution(0, 'score', '3'))
    const expected = makeFixture({solutions: [{score: 3}]})
    ensure.equal(reduced, expected)
  })

  it('updates solution text', () => {
    const item = makeFixture()
    const reduced = reduce(item, subActions.updateSolution(0, 'text', 'This is new'))
    const expected = makeFixture({solutions: [{text: 'This is new'}]})
    ensure.equal(reduced, expected)
  })

  it('updates solution score', () => {
    const item = makeFixture()
    const reduced = reduce(item, subActions.updateSolution(0, 'score', 3))
    const expected = makeFixture({solutions: [{score: 3}, {}]})
    ensure.equal(reduced, expected)
  })

  it('updates solution caseSensitive', () => {
    const item = makeFixture()
    const reduced = reduce(item, subActions.updateSolution(1, 'caseSensitive', true))
    const expected = makeFixture({solutions: [{}, {caseSensitive: true}]})
    ensure.equal(reduced, expected)
  })

  it('updates solutions and deletable property on solution add', () => {
    const item = makeFixture()
    const reduced = reduce(item, subActions.addSolution())
    const expected = makeFixture({
      solutions: [
        {},
        {},
        {
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
    const reduced = reduce(item, subActions.removeSolution(0))
    const expected = makeFixture({}, false)
    expected.solutions.splice(0, 1)
    expected.solutions[0]._deletable = false
    ensure.equal(reduced, expected)
  })
})

describe('Words validator', () => {
  before(mockTranslator)
  const validate = definition.editor.validate
  it('checks solutions text are not empty', () => {
    const errors = validate({
      solutions: [
        {
          text:'',
          feedback:'',
          caseSensitive: false,
          score: 2
        }
      ]
    })
    ensure.equal(errors, {
      solutions: 'words_empty_text_error'
    })
  })

  it('checks solutions scores are not empty', () => {
    const errors = validate({
      solutions: [
        {
          text:'A',
          feedback:'',
          caseSensitive: false,
          score: ''
        }
      ]
    })
    ensure.equal(errors, {
      solutions: 'words_score_not_valid'
    })
  })

  it('checks solutions scores are valid numbers', () => {
    const errors = validate({
      solutions: [
        {
          text:'A',
          feedback:'',
          caseSensitive: false,
          score: []
        }
      ]
    })
    ensure.equal(errors, {
      solutions: 'words_score_not_valid'
    })
  })

  it('checks at least one solution has a score greater than 0', () => {
    const errors = validate({
      solutions: [
        {
          text:'A',
          feedback:'',
          caseSensitive: false,
          score: 0
        }
      ]
    })
    ensure.equal(errors, {
      solutions: 'words_no_valid_solution'
    })
  })

  it('returns no errors if item is valid', () => {
    const errors = validate({
      solutions: [
        {
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
    shallow(<Words item={{foo:'baz'}}/>)
    ensure.missingProps('Words', ['onChange', 'item.id'])
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

    const form = mount(
      <Words
        item={{
          id: '1',
          content: 'Question?',
          solutions: [
            {
              text:'A',
              feedback:'',
              caseSensitive: false,
              score: 2,
              _deletable: false
            }
          ],
          _wordsCaseSensitive: true
        }}
        onChange={value => updatedValue = value}
      />
    )
    ensure.propTypesOk()

    const text = form.find('input[type="text"]#solution-0-text')
    ensure.equal(text.length, 1, 'has text input')
    // @TODO find a way to test that changes are handled

    const score = form.find('input#solution-0-score')
    ensure.equal(score.length, 1, 'has score input')
    score.simulate('change', {target: {value: 5}})
    ensure.equal(updatedValue.value, 5)
    ensure.equal(updatedValue.property, 'score')

    // @TODO find a way to test that the click action result in a new form-row
    const addBtn = form.find('button#add-word-button')
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
        text:'A',
        caseSensitive: false,
        score: 1,
        feedback: '',
        _deletable: true
      },
      {
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
