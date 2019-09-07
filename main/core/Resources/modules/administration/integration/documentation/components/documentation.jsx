import React from 'react'
import SwaggerUI from 'swagger-ui-react'

import {url} from '#/main/app/api/router'

const Documentation = () =>
  <SwaggerUI
    url={url(['apiv2_swagger_get'])}
  />

export {
  Documentation
}
