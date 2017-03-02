import React from 'react'
import freeze from 'deep-freeze'
import merge from 'lodash/merge'
import {shallow, mount} from 'enzyme'
import {spyConsole, renew, ensure, mockTranslator} from './../../utils/test'
import {actions} from './../../quiz/editor/actions'
import {actions as subActions} from './editor'
import definition from './index'

describe('Open reducer', () => {
  const reduce = definition.editor.reduce

  it('augments and decorates base question on creation', () => {
    const item = {
      id: '1',
      type: 'application/x.open+json',
      content: 'Question?'
    }
    const reduced = reduce(item, actions.createItem('1', 'application/x.open+json'))
    ensure.equal(reduced, {
      id: '1',
      type: 'application/x.open+json',
      contentType: 'text',
      content: 'Question?',
      maxLength: 0,
      score:{
        type: 'manual',
        max: 0
      },
      solutions: []
    })
  })

  it('updates base properties', () => {
    const item = makeFixture()
    const reduced = reduce(item, subActions.update('maxLength', 255))
    const expected = makeFixture({maxLength: 255})
    ensure.equal(reduced, expected)
  })

  it('sanitizes incoming data', () => {
    const item = makeFixture()
    const reduced = reduce(item, subActions.update('maxScore', '10'))
    const expected = makeFixture({score: {max: 10}})
    ensure.equal(reduced, expected)
  })
})

describe('Open validator', () => {
  before(mockTranslator)

  const validate = definition.editor.validate

  it('checks maxScore is greater or equal to zero', () => {
    const errors = validate({
      score:{max: -1},
      maxLength:0
    })
    ensure.equal(errors, {
      maxScore: 'This value should be 0 or more.'
    })
  })

  it('checks maxScore is not empty', () => {
    const errors = validate({
      score:{max: ''},
      maxLength:0
    })
    ensure.equal(errors, {
      maxScore: 'This value should not be blank.'
    })
  })

  it('checks maxScore is a number', () => {
    const errors = validate({
      score:{max: []},
      maxLength:0
    })
    ensure.equal(errors, {
      maxScore: 'This value should be a valid number.'
    })
  })

  it('checks maxLength is greater or equal to zero', () => {
    const errors = validate({
      score:{max: 0},
      maxLength: -1
    })
    ensure.equal(errors, {
      maxLength: 'This value should be 0 or more.'
    })
  })

  it('checks maxLength is not empty', () => {
    const errors = validate({
      score:{max: 0},
      maxLength: null
    })
    ensure.equal(errors, {
      maxLength: 'This value should not be blank.'
    })
  })

  it('checks maxLength is a number', () => {
    const errors = validate({
      score:{max: 0},
      maxLength:[]
    })
    ensure.equal(errors, {
      maxLength: 'This value should be a valid number.'
    })
  })

  it('returns no errors if item is valid', () => {
    const errors = validate({
      score:{max: 0},
      maxLength:0
    })
    ensure.equal(errors, {})
  })
})

describe('<Open/>', () => {
  const Open = definition.editor.component

  beforeEach(() => {
    spyConsole.watch()
    renew(Open, 'Open')
  })
  afterEach(spyConsole.restore)

  it('has required props', () => {
    shallow(<Open item={{foo:'baz', score:{}}}/>)
    ensure.missingProps('Open', ['onChange', 'item.id'])
  })

  it('has typed props', () => {
    shallow(
      <Open
        item={{
          id: [],
          score: [],
          maxLength: []
        }}
        onChange={false}
      />
    )
    ensure.invalidProps('Open', ['item.id', 'onChange'])
  })

  it('renders appropriate fields and handle changes', () => {
    let updatedValue = null

    const form = mount(
      <Open
        item={{
          id: '1',
          content: 'Question?',
          maxLength: 255,
          score: {
            type: 'manual',
            max: 10
          }
        }}
        onChange={value => updatedValue = value}
      />
    )
    ensure.propTypesOk()

    const maxScore = form.find('input#item-1-maxScore')
    ensure.equal(maxScore.length, 1, 'has maxScore input')
    maxScore.simulate('change', {target: {value: 10}})
    ensure.equal(updatedValue.value, 10)
    ensure.equal(updatedValue.property, 'maxScore')

    const maxLength = form.find('input#item-1-maxLength')
    ensure.equal(maxLength.length, 1, 'has maxLength input')
    maxLength.simulate('change', {target: {value: 255}})
    ensure.equal(updatedValue.value, 255)
    ensure.equal(updatedValue.property, 'maxLength')
  })
})

function makeFixture(props = {}, frozen = true) {
  const fixture = merge({
    id: '1',
    contentType: 'text',
    type: 'application/x.open+json',
    content: 'Question?',
    score: {
      type: 'manual',
      max: 10
    },
    solutions: [],
    maxLength: 0
  }, props)

  return frozen ? freeze(fixture) : fixture
}
