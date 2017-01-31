import assert from 'assert'
import {assertEqual} from './../utils/test'
import {
  registerItemType,
  listItemMimeTypes,
  getDefinition,
  getDecorators,
  resetTypes
} from './../items/item-types'

describe('Registering an item type', () => {
  afterEach(resetTypes)

  it('throws if item name type is absent or invalid', () => {
    assert.throws(() => {
      registerItemType({})
    }, /name is mandatory/)
    assert.throws(() => {
      registerItemType({name: []})
    }, /name must be a string/i)
  })

  it('throws if item mime type is absent or invalid', () => {
    assert.throws(() => {
      registerItemType({
        name: 'foo',
        player:{
          component: () => 'player',
          reduce: item => item
        },
        editor: {
          component: () => 'editor',
          reduce: item => item
        }
      })
    }, /mime type is mandatory/)
    assert.throws(() => {
      registerItemType({
        name: 'foo',
        type: [],
        player:{
          component: () => 'player',
          reduce: item => item
        },
        editor: {
          component: () => 'editor',
          reduce: item => item
        }
      })
    }, /mime type must be a string/i)
  })

  it('throws if item editor is absent', () => {
    assert.throws(() => {
      registerItemType({
        name: 'foo',
        type: 'foo/bar',
        player:{
          component: () => 'player',
          reduce: item => item
        }
      })
    }, /editor is mandatory/i)
  })

  it('throws if item editor component is absent', () => {
    assert.throws(() => {
      registerItemType({
        name: 'foo',
        type: 'foo/bar',
        player:{
          component: () => 'player',
          reduce: item => item
        },
        editor: {
          reduce: item => item
        }
      })
    }, /editor component is mandatory/i)
  })

  it('throws if item paper component is absent', () => {
    assert.throws(() => {
      registerItemType({
        name: 'foo',
        type: 'foo/bar',
        player:{
          component: () => 'player',
          reduce: item => item
        },
        editor: {
          component: () => 'editor',
          reduce: item => item
        }
      })
    }, /paper component is mandatory/i)
  })

  it('registers valid types as expected', () => {
    registerItemType(validDefinitionFixture())

    assertEqual(listItemMimeTypes(), ['foo/bar'])

    const def = getDefinition('foo/bar')

    assertEqual(def.name, 'foo')
    assertEqual(def.type, 'foo/bar')
    assertEqual(def.editor.component(), 'editor')
    assertEqual(def.editor.reduce('item'), 'item')
    assertEqual(def.player(), 'player')
  })

  it('throws if item type is already registered', () => {
    registerItemType(validDefinitionFixture())
    assert.throws(() => {
      registerItemType(validDefinitionFixture())
    }, /already registered/i)
  })

  it('defaults items to questions', () => {
    registerItemType(validDefinitionFixture())
    assertEqual(getDefinition('foo/bar').question, true)
  })

  it('defaults decorators to identity functions', () => {
    registerItemType(validDefinitionFixture())
    assertEqual(getDefinition('foo/bar').editor.decorate('item'), 'item')
  })

  it('defaults validators to noop functions', () => {
    registerItemType(validDefinitionFixture())
    assertEqual(getDefinition('foo/bar').editor.validate('item'), {})
  })

  it('throws if definition contains extra properties', () => {
    const definition = Object.assign({}, validDefinitionFixture(), {bar: 'baz'})
    assert.throws(() => {
      registerItemType(definition)
    }, /unknown property 'bar' in 'foo' definition/i)
  })
})

describe('Getting a type definition', () => {
  afterEach(resetTypes)

  it('throws if type does not exist', () => {
    assert.throws(() => {
      getDefinition('unknown/type')
    }, /unknown item type/i)
  })

  it('returns the full definition', () => {
    registerItemType(validDefinitionFixture())
    const def = getDefinition('foo/bar')
    assertEqual(def.type, 'foo/bar')
    assertEqual(def.editor.component(), 'editor')
    assertEqual(def.editor.reduce('item'), 'item')
    assertEqual(def.editor.decorate('item'), 'item')
    assertEqual(def.editor.validate('item'), {})
    assertEqual(def.player(), 'player')
  })
})

describe('Getting type decorates', () => {
  afterEach(resetTypes)

  it('sorts decorators by type', () => {
    registerItemType(validDefinitionFixture())
    const decorators = getDecorators()
    assertEqual(Object.keys(decorators), ['foo/bar'])
    assertEqual(typeof decorators['foo/bar'], 'function')
  })
})

function validDefinitionFixture() {
  return {
    name: 'foo',
    type: 'foo/bar',
    editor: {
      component: () => 'editor',
      reduce: item => item
    },
    player: () => 'player',
    paper: () => 'paper'
  }
}
