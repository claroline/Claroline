import React, {Component} from 'react'

import SwaggerUI from 'swagger-ui-react'
import {url} from '#/main/app/api/router'

class DocumentationComponent extends Component {
  componentDidMount() {

  }

  render() {
    return(<SwaggerUI url={url(['apiv2_swagger_get'])}/>)
  }
}

export {
  DocumentationComponent
}
