import {connect} from 'react-redux'

import {selectors as configSelectors} from '#/main/app/config/store'

import {SecurityPage as SecurityPageComponent} from '#/main/app/security/components/page'

const SecurityPage = connect(
  (state) => ({
    // platform parameters
    logo: configSelectors.param(state, 'logo'),
    name: configSelectors.param(state, 'name'),
    description: 'Etiam sit amet ultrices ligula. Vestibulum interdum nec lacus rhoncus tincidunt. Praesent et eleifend lorem, sed pellentesque ex. Ut tempus sapien id semper vehicula. Proin maximus lacus quis scelerisque aliquet.'
  })
)(SecurityPageComponent)

export {
  SecurityPage
}
