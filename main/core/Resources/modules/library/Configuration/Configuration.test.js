import Configuration from './Configuration'
import assert from 'assert'

import fullAction from './test-stubs/actions-full'
import partialAction from './test-stubs/actions-partial'

describe('Configuration', () => {
  it('Create a configuration data structure', () => {
    Configuration.setConfig(fullAction)
    const buttons = Configuration.getUsersAdministrationActions()
    assert.equal(2, buttons.length, 'There should be only 2 actions')
    var struct = [fullAction.p1.actions[0], fullAction.p2.actions[1]]
    assert.deepEqual(struct, buttons, 'The data structure returned is invalid')
  })

  it('Test the default properties of the actions', () => {
    Configuration.setConfig(partialAction)
    const buttons = Configuration.getUsersAdministrationActions()
    assert.equal('#', buttons[0].url, 'Default href is #')
    assert.equal('fa fa-fw fa-cog', buttons[0].icon, 'Default class is fa fa-cog')
  })
})
