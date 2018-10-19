import React from 'react'

import {trans} from '#/main/app/intl/translation'

const Editor = () =>
  <div className="editor-content alert alert-info">
    {trans('no_configuration_for_resource')}
  </div>

export {
  Editor
}