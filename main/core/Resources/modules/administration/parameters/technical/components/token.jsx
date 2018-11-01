import React from 'react'
import {connect} from 'react-redux'

const TokenComponent = () =>
  <div>
    Token
  </div>


TokenComponent.propTypes = {
}

const Token = connect(
  null,
  () => ({ })
)(TokenComponent)

export {
  Token
}
