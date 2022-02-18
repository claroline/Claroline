import React from 'react'
import {PropTypes as T} from 'prop-types'
import isEmpty from 'lodash/isEmpty'

import {trans} from '#/main/app/intl/translation'
import {Button} from '#/main/app/action/components/button'
import {URL_BUTTON} from '#/main/app/buttons'
import {ContentPlaceholder} from '#/main/app/content/components/placeholder'

const ImportSamples = (props) => {
  if (isEmpty(props.samples)) {
    return (
      <ContentPlaceholder
        style={{marginTop: 20}}
        icon="fa fa-file-csv"
        title={trans('no_sample', {}, 'transfer')}
      />
    )
  }

  return (
    <div className="list-group list-group-striped" style={{marginTop: 20}}>
      {props.samples.map(sample =>
        <Button
          key={sample}
          className="list-group-item"
          type={URL_BUTTON}
          icon="fa fa-fw fa-file-csv"
          label={sample}
          target={['apiv2_transfer_import_sample', {
            format: props.format,
            entity: props.entity,
            name: props.action,
            sample: sample
          }]}
        />
      )}
    </div>
  )
}

ImportSamples.propTypes = {
  format: T.string,
  entity: T.string,
  action: T.string,
  samples: T.arrayOf(T.string)
}

ImportSamples.defaultProps = {
  samples: []
}

export {
  ImportSamples
}
