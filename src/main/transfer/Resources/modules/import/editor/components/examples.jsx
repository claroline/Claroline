import React from 'react'

import {trans} from '#/main/app/intl'
import {Button} from '#/main/app/action'
import {EditorPage} from '#/main/app/editor'
import {URL_BUTTON} from '#/main/app/buttons'

const ImportEditorExamples = (props) =>
  <EditorPage
    title={trans('examples', {}, 'transfer')}
    help={trans('transfer_examples_help', {}, 'transfer')}
    definition={[
      {
        name: 'examples',
        title: trans('examples', {}, 'transfer'),
        primary: true,
        render: () => (
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
    ]}
  />

export {
  ImportEditorExamples
}
