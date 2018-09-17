import {connect} from 'react-redux'

import {withReducer} from '#/main/app/store/components/withReducer'

import {reducer, selectors} from '#/plugin/collecticiel/resources/collecticiel/store'
import {CollecticielResource as CollecticielResourceComponent} from '#/plugin/collecticiel/resources/collecticiel/components/resource'

const CollecticielResource = withReducer(selectors.STORE_NAME, reducer)(
  connect(
    (state) => ({
      dropzone: selectors.dropzone(state)
    })
  )(CollecticielResourceComponent)
)

export {
  CollecticielResource
}
