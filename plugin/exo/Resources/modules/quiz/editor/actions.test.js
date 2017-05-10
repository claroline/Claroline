import thunk from 'redux-thunk'
import assert from 'assert'
import {ensure} from '#/main/core/tests'
import configureMockStore from 'redux-mock-store'
import {TYPE_STEP} from './../enums'
import {
  STEP_ITEM_DELETE,
  ITEM_DELETE,
  OBJECT_NEXT,
  STEP_DELETE,
  actions
} from './actions'

describe('#createItem', () => {
  it('generates a unique id for each item', () => {
    const item1 = actions.createItem('1', 'application/choice.x+json')
    const item2 = actions.createItem('3', 'application/choice.x+json')
    ensure.equal(typeof item1.id, 'string', 'Item id must a string')
    ensure.equal(typeof item2.id, 'string', 'Item id must be a string')
    assert.notEqual(item1.id, item2.id, 'Item ids must be unique')
  })
})

describe('#createStep', () => {
  it('generates a unique id for each step', () => {
    const step1 = actions.createStep()
    const step2 = actions.createStep()
    ensure.equal(typeof step1.id, 'string', 'Step id must a string')
    ensure.equal(typeof step2.id, 'string', 'Step id must be a string')
    assert.notEqual(step1.id, step2.id, 'Item ids must be unique')
  })
})

describe('#deleteStepAndItems', () => {
  it('dispatches both step and items deletion', () => {
    const mockStore = configureMockStore([thunk])
    const store = mockStore({
      quiz: {steps: ['1', '2']},
      steps: {
        '1': {id: '1', items: ['a']},
        '2': {id: '2', items: ['b', 'c']}
      },
      editor: {
        currentObject: {
          id: '2',
          type: TYPE_STEP
        }
      }
    })
    const expectedActions = [
      { type: OBJECT_NEXT, object: {id: '1', type: TYPE_STEP}},
      { type: STEP_ITEM_DELETE, id: 'b', stepId: '2' },
      { type: ITEM_DELETE, id: 'b' },
      { type: STEP_ITEM_DELETE, id: 'c', stepId: '2' },
      { type: ITEM_DELETE, id: 'c' },
      { type: STEP_DELETE, id: '2' }
    ]
    store.dispatch(actions.deleteStepAndItems('2'))
    ensure.equal(store.getActions(), expectedActions)
  })
})
