import React from 'react'
import {PropTypes as T} from 'prop-types'
import {connect} from 'react-redux'

import {ContentHtml} from '#/main/app/content/components/html'

import {selectors} from '#/integration/big-blue-button/resources/bbb/store'
import {BBB as BBBType} from '#/integration/big-blue-button/resources/bbb/prop-types'
import {ResourcePage} from '#/main/core/resource'

const EndComponent = (props) =>
  <ResourcePage>
    <ContentHtml>
      {props.bbb.endMessage}
    </ContentHtml>
  </ResourcePage>

EndComponent.propTypes = {
  bbb: T.shape(BBBType.propTypes).isRequired
}

const End = connect(
  (state) => ({
    bbb: selectors.bbb(state)
  })
)(EndComponent)

export {
  End
}
