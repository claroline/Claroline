import {
  ensure,
  describeComponent,
  shallowComponent
} from '#/main/core/scaffolding/tests'

import {ContentHelp} from '#/main/app/content/components/help'

describeComponent('ContentHelp', ContentHelp,
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
      const block = shallowComponent(ContentHelp, 'ContentHelp', {
        help: 'HELP'
      })

      ensure.propTypesOk()
      ensure.equal(block.text(), 'HELP')
    })
  }
)
