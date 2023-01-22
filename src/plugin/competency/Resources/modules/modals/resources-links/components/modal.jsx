import React from 'react'
import {PropTypes as T} from 'prop-types'
import omit from 'lodash/omit'

import {trans} from '#/main/app/intl/translation'
import {CALLBACK_BUTTON} from '#/main/app/buttons'
import {Modal} from '#/main/app/overlays/modal/components/modal'
import {Button} from '#/main/app/action/components/button'
import {ModalButton} from '#/main/app/buttons/modal/containers/button'

import {
  Competency as CompetencyType,
  Ability as AbilityType
} from '#/plugin/competency/tools/evaluation/prop-types'
import {MODAL_COMPETENCIES_PICKER} from '#/plugin/competency/modals/competencies'
import {MODAL_ABILITIES_PICKER} from '#/plugin/competency/modals/abilities'

const ResourcesLinksModal = (props) =>
  <Modal
    {...omit(props, 'nodeId', 'competencies', 'abilities', 'loadCompetencies', 'associateCompetency', 'dissociateCompetency', 'loadAbilities', 'associateAbility', 'dissociateAbility')}
    icon="fa fa-fw fa-atom"
    title={trans('competencies.associated', {}, 'competency')}
    onEntering={() => {
      props.loadCompetencies(props.nodeId)
      props.loadAbilities(props.nodeId)
    }}
  >
    <ModalButton
      className="btn btn-competencies-primary"
      style={{margin: 10}}
      primary={true}
      modal={[MODAL_COMPETENCIES_PICKER, {
        selectAction: (selected) => ({
          type: CALLBACK_BUTTON,
          callback: () => {
            props.associateCompetency(props.nodeId, selected[0])
          }
        })
      }]}
    >
      {trans('competency.associate', {}, 'competency')}
    </ModalButton>
    <ModalButton
      className="btn btn-abilities-primary"
      style={{margin: 10}}
      primary={true}
      modal={[MODAL_ABILITIES_PICKER, {
        selectAction: (selected) => ({
          type: CALLBACK_BUTTON,
          callback: () => {
            props.associateAbility(props.nodeId, selected[0])
          }
        })
      }]}
    >
      {trans('ability.associate', {}, 'competency')}
    </ModalButton>
    <div className="modal-body">
      {0 < props.competencies.length &&
        <h3>{trans('competencies', {}, 'competency')}</h3>
      }
      {0 !== props.competencies.length &&
        <ul
          className="competencies"
          style={{
            listStyleType: 'none',
            margin: 0,
            padding: 0
          }}
        >
          {props.competencies.map(competency =>
            <li
              key={competency.id}
              className="competency"
              style={{
                display: 'flex',
                justifyContent: 'stretch',
                border: '1px solid #777',
                marginBottom: '10px',
                borderRadius: '4px'
              }}
            >
              <div
                className="competency-name"
                style={{
                  padding: '5px 10px',
                  display: 'flex',
                  alignItems: 'center',
                  flexWrap: 'wrap'
                }}
              >
                {competency.name}
              </div>

              <Button
                className="competency-action btn btn-link"
                style={{
                  marginLeft: 'auto'
                }}
                type={CALLBACK_BUTTON}
                icon="fa fa-times"
                label={trans('delete', {}, 'actions')}
                tooltip="left"
                callback={() => props.dissociateCompetency(props.nodeId, competency)}
              />
            </li>
          )}
        </ul>
      }

      {0 < props.abilities.length &&
        <h3>{trans('abilities', {}, 'competency')}</h3>
      }
      {0 !== props.abilities.length &&
        <ul
          className="competencies"
          style={{
            listStyleType: 'none',
            margin: 0,
            padding: 0
          }}
        >
          {props.abilities.map(ability =>
            <li
              key={ability.id}
              className="competency"
              style={{
                display: 'flex',
                justifyContent: 'stretch',
                border: '1px solid #777',
                marginBottom: '10px',
                borderRadius: '4px'
              }}
            >
              <div
                className="competency-name"
                style={{
                  padding: '5px 10px',
                  display: 'flex',
                  alignItems: 'center',
                  flexWrap: 'wrap'
                }}
              >
                {ability.name}
              </div>

              <Button
                className="competency-action btn btn-link"
                style={{
                  marginLeft: 'auto'
                }}
                type={CALLBACK_BUTTON}
                icon="fa fa-times"
                label={trans('delete', {}, 'actions')}
                tooltip="left"
                callback={() => props.dissociateAbility(props.nodeId, ability)}
              />
            </li>
          )}
        </ul>
      }
    </div>
  </Modal>

ResourcesLinksModal.propTypes = {
  nodeId: T.string.isRequired,
  competencies: T.arrayOf(T.shape(CompetencyType.propTypes)),
  abilities: T.arrayOf(T.shape(AbilityType.propTypes)),
  loadCompetencies: T.func.isRequired,
  associateCompetency: T.func.isRequired,
  dissociateCompetency: T.func.isRequired,
  loadAbilities: T.func.isRequired,
  associateAbility: T.func.isRequired,
  dissociateAbility: T.func.isRequired
}

ResourcesLinksModal.defaultProps = {
  competencies: [],
  abilities: []
}

export {
  ResourcesLinksModal
}
