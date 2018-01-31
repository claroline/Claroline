import {
  ensure,
  describeComponent,
  shallowComponent
} from '#/main/core/scaffolding/tests'

import {HelpBlock} from './help-block.jsx'

describeComponent('HelpBlock', HelpBlock,
  // required props
  [
    'help'
  ],
  // invalid props
  {
    help: []
  },
  // valid props
  {
    help: 'HELP'
  },
  // custom tests
  () => {
    it('renders a help text', () => {
      const block = shallowComponent(HelpBlock, 'HelpBlock', {
        help: 'HELP'
      })

      ensure.propTypesOk()
      ensure.equal(block.text(), 'HELP')
    })
  }
)
