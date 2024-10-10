import React from 'react'
import {connect} from 'react-redux'
import {PropTypes as T} from 'prop-types'

import {trans} from '#/main/app/intl/translation'
import {FormSections, FormSection} from '#/main/app/content/form/components/sections'

import {DropzoneType, DropType} from '#/plugin/drop-zone/resources/dropzone/prop-types'
import {selectors} from '#/plugin/drop-zone/resources/dropzone/store/selectors'
import {constants} from '#/plugin/drop-zone/resources/dropzone/constants'
import {actions} from '#/plugin/drop-zone/resources/dropzone/correction/actions'
import {Documents} from '#/plugin/drop-zone/resources/dropzone/components/documents'
import {CorrectionCreation} from '#/plugin/drop-zone/resources/dropzone/correction/components/correction-creation'
import {CorrectionRow} from '#/plugin/drop-zone/resources/dropzone/correction/components/correction-row'
import {ResourcePage} from '#/main/core/resource'
import {ContentLoader} from '#/main/app/content/components/loader'

const Corrections = props =>
  <FormSections>
    <FormSection
      title={trans('corrections', {}, 'dropzone')}
    >
      <table className="table corrections-table">
        <thead>
          <tr>
            <th></th>
            <th>{trans('corrector', {}, 'dropzone')}</th>
            <th>{trans('start_date', {}, 'platform')}</th>
            <th>{trans('end_date', {}, 'platform')}</th>
            <th>{trans('score', {}, 'platform')}</th>
            <th>{trans('actions', {}, 'platform')}</th>
          </tr>
        </thead>

        <tbody>
          {props.corrections.map(c =>
            <CorrectionRow
              key={`correction-row-${c.id}`}
              correction={c}
            />
          )}
        </tbody>
      </table>
    </FormSection>
  </FormSections>

Corrections.propTypes = {
  corrections: T.array
}

const Drop = (props) =>
  <ResourcePage>
    {!props.drop &&
      <ContentLoader />
    }

    {props.drop &&
      <>
        <h2>
          {trans(
            'drop_from',
            {'name': props.dropzone.parameters.dropType === constants.DROP_TYPE_USER ?
                `${props.drop.user.firstName} ${props.drop.user.lastName}` :
                props.drop.teamName
            },
            'dropzone'
          )}
        </h2>

        <Documents
          documents={props.drop.documents}
          showUser={props.dropzone.parameters.dropType === constants.DROP_TYPE_TEAM}
        />

        {props.drop.corrections && props.drop.corrections.length > 0 &&
          <Corrections
            corrections={props.drop.corrections || []}
          />
        }

        {props.drop.finished &&
          <CorrectionCreation {...props}/>
        }
      </>
    }
  </ResourcePage>

Drop.propTypes = {
  dropzone: T.shape(DropzoneType.propTypes),
  drop: T.shape(DropType.propTypes),
  saveCorrection: T.func.isRequired
}

const ConnectedDrop = connect(
  (state) => ({
    dropzone: selectors.dropzone(state),
    drop: selectors.currentDrop(state)
  }),
  (dispatch) => ({
    saveCorrection: (correction) => dispatch(actions.saveCorrection(correction))
  })
)(Drop)

export {
  ConnectedDrop as Drop
}
