import React, {useCallback, useState} from 'react'
import {useDispatch, useSelector} from 'react-redux'
import classes from 'classnames'
import get from 'lodash/get'
import isEmpty from 'lodash/isEmpty'
import {Collapse} from 'react-bootstrap'

import {trans} from '#/main/app/intl'
import {Button} from '#/main/app/action'
import {CALLBACK_BUTTON, MODAL_BUTTON} from '#/main/app/buttons'
import {Html} from '#/main/app/components/html'
import {DataMicro} from '#/main/app/data/components/micro'
import {EditorPage} from '#/main/app/editor'

import {MODAL_ROLES} from '#/main/community/modals/roles'
import {actions, selectors} from '#/main/evaluation/sequence/editor/store'
import {selectors as sequenceSelectors} from '#/main/evaluation/sequence/store'

const restrictedByDates = (formData) => get(formData, 'restrictions.enableDates') || !isEmpty(get(formData, 'restrictions.dates'))
const restrictByCode = (formData) => get(formData, 'restrictions.enableCode') || !!get(formData, 'restrictions.code')

const Assignments = () => {
  const dispatch = useDispatch()
  const [showHelp, setShowHelp] = useState(false)

  const workspace = useSelector(sequenceSelectors.workspace)
  const assignments = useSelector(selectors.assignments)

  const updateAssignments = useCallback((assignments) => {
    dispatch(actions.update(assignments, 'assignments'))
  }, [workspace.id])

  const updateAssignment = useCallback((assignment, assignmentIndex) => {
    const newAssignments = [].concat(assignments)
    newAssignments[assignmentIndex] = assignment

    dispatch(actions.update(newAssignments, 'assignments'))
  }, [assignments.map(a => a.role.id).join('-')])

  const deleteAssignment = useCallback((assignmentIndex) => {
    const newAssignments = [].concat(assignments)
    newAssignments.splice(assignmentIndex, 1)

    dispatch(actions.update(newAssignments, 'assignments'))
  }, [assignments.map(a => a.role.id).join('-')])

  return (
    <>
      <div className="d-flex flex-row gap-1" role="presentation">
        <Button
          className="btn btn-primary"
          type={MODAL_BUTTON}
          modal={[MODAL_ROLES, {
            multiple: true,
            url: !isEmpty(workspace) ?
              ['apiv2_workspace_list_roles', {id: workspace.id}] :
              ['apiv2_role_list'],
            contextId: workspace.id,
            selectAction: (selected) => ({
              type: CALLBACK_BUTTON,
              callback: () => {
                // removes roles already defined in assignments
                const toUpdate = selected.filter(s => -1 === assignments.findIndex(assignment => get(s, 'id') === get(assignment, 'role.id')))

                return updateAssignments([].concat(assignments, toUpdate.map(role => ({
                  role: role,
                  required: false,
                  scored: false
                }))))
              }
            })
          }]}
          label={trans('Ajouter des participants')}
        />

        <Button
          className="btn btn-text-body focus-ring"
          type={CALLBACK_BUTTON}
          icon="fa fa-fw fa-question-circle"
          label={trans(showHelp ? 'hide_help' : 'show_help', {}, 'actions')}
          callback={() => setShowHelp(!showHelp)}
        />
      </div>

      <Collapse in={showHelp}>
        <dl className="p-3 mb-0 bg-body-tertiary rounded-3 gap-0">
          <dt className="text-uppercase fw-bolder fs-base text-body">{trans('sequence_available', {}, 'evaluation')}</dt>
          <dd className="mb-3">
            <div className="py-1 d-flex flex-row align-items-baseline" role="presentation">
              <span className="fa fa-fw fa-check-circle text-primary me-2" aria-hidden={true} />
              <Html>{trans('sequence_available_help', {}, 'evaluation')}</Html>
            </div>
            <div className="py-1 d-flex flex-row align-items-baseline text-body-secondary" role="presentation">
              <span className="fa fa-fw fa-times-circle text-body-tertiary me-2" aria-hidden={true} />
              <Html>{trans('sequence_not_required_help', {}, 'evaluation')}</Html>
            </div>
            <div className="py-1 d-flex flex-row align-items-baseline text-body-secondary" role="presentation">
              <span className="fa fa-fw fa-times-circle text-body-tertiary me-2" aria-hidden={true} />
              <Html>{trans('sequence_not_scored_help', {}, 'evaluation')}</Html>
            </div>
          </dd>

          <dt className="text-uppercase fw-bolder fs-base text-body">{trans('sequence_required', {}, 'evaluation')}</dt>
          <dd className="mb-3">
            <div className="py-1 d-flex flex-row align-items-baseline" role="presentation">
              <span className="fa fa-fw fa-check-circle text-primary me-2" aria-hidden={true} />
              <Html>{trans('sequence_available_help', {}, 'evaluation')}</Html>
            </div>
            <div className="py-1 d-flex flex-row align-items-baseline" role="presentation">
              <span className="fa fa-fw fa-check-circle text-primary me-2" aria-hidden={true} />
              <Html>{trans('sequence_required_help', {}, 'evaluation')}</Html>
            </div>
            <div className="py-1 d-flex flex-row align-items-baseline text-body-secondary" role="presentation">
              <span className="fa fa-fw fa-times-circle text-body-tertiary me-2" aria-hidden={true} />
              <Html>{trans('sequence_not_scored_help', {}, 'evaluation')}</Html>
            </div>
          </dd>

          <dt className="text-uppercase fw-bolder fs-base text-body">{trans('sequence_scored', {}, 'evaluation')}</dt>
          <dd className="mb-0">
            <div className="py-1 d-flex flex-row align-items-baseline" role="presentation">
              <span className="fa fa-fw fa-check-circle text-primary me-2" aria-hidden={true} />
              <Html>{trans('sequence_available_help', {}, 'evaluation')}</Html>
            </div>
            <div className="py-1 d-flex flex-row align-items-baseline" role="presentation">
              <span className="fa fa-fw fa-check-circle text-primary me-2" aria-hidden={true} />
              <Html>{trans('sequence_required_help', {}, 'evaluation')}</Html>
            </div>
            <div className="py-1 d-flex flex-row align-items-baseline" role="presentation">
              <span className="fa fa-fw fa-check-circle text-primary me-2" aria-hidden={true} />
              <Html>{trans('sequence_scored_help', {}, 'evaluation')}</Html>
            </div>
          </dd>
        </dl>
      </Collapse>

      {!isEmpty(assignments) &&
        <ul className="list-group list-group-flush">
          {assignments.map((assignment, index) =>
            <li key={assignment.role.id} className="list-group-item d-flex flex-row flex-wrap gap-3 px-0">
              <DataMicro object={{
                name: trans(assignment.role.translationKey)
              }} />

              <div role="radiogroup" className="ms-auto gap-1 d-flex flex-row fs-sm">
                <input
                  type="radio"
                  className="btn-check"
                  name={get(assignment, 'role.id')}
                  id={`${get(assignment, 'role.id')}-optional`}
                  checked={!assignment.required && !assignment.scored}
                  onChange={() => {
                    updateAssignment(Object.assign({}, assignment, {
                      required: false,
                      scored: false
                    }), index)
                  }}
                />
                <label
                  className={classes('py-1 px-2 border focus-ring btn btn-text-body btn-sm', {
                    'border-primary text-primary-emphasis bg-primary-subtle': !assignment.required && !assignment.scored
                  })}
                  htmlFor={`${get(assignment, 'role.id')}-optional`}
                >
                  {trans('sequence_available', {}, 'evaluation')}
                </label>

                <input
                  type="radio"
                  className="btn-check"
                  name={get(assignment, 'role.id')}
                  id={`${get(assignment, 'role.id')}-required`}
                  checked={assignment.required && !assignment.scored}
                  onChange={() => {
                    updateAssignment(Object.assign({}, assignment, {
                      required: true,
                      scored: false
                    }), index)
                  }}
                />
                <label
                  className={classes('py-1 px-2 border focus-ring btn btn-text-body btn-sm', {
                    'border-primary text-primary-emphasis bg-primary-subtle': assignment.required && !assignment.scored
                  })}
                  htmlFor={`${get(assignment, 'role.id')}-required`}
                >
                  {trans('sequence_required', {}, 'evaluation')}
                </label>

                <input
                  type="radio"
                  className="btn-check"
                  name={get(assignment, 'role.id')}
                  id={`${get(assignment, 'role.id')}-scored`}
                  checked={assignment.scored}
                  onChange={() => {
                    updateAssignment(Object.assign({}, assignment, {
                      required: true,
                      scored: true
                    }), index)
                  }}
                />
                <label
                  className={classes('py-1 px-2 border focus-ring btn btn-text-body btn-sm', {
                    'border-primary text-primary-emphasis bg-primary-subtle': assignment.scored
                  })}
                  htmlFor={`${get(assignment, 'role.id')}-scored`}
                >
                  {trans('sequence_scored', {}, 'evaluation')}
                </label>
              </div>

              <Button
                className="btn btn-link mx-n2"
                type={CALLBACK_BUTTON}
                label={trans('remove', {}, 'actions')}
                callback={() => deleteAssignment(index)}
                size="sm"
              />
            </li>
          )}
        </ul>
      }
    </>
  )
}

const SequenceEditorPermissions = () => {
  const dispatch = useDispatch()
  const updateProp = useCallback((prop, value) => {
    dispatch(actions.update(value, prop))
  }, [selectors.STORE_NAME])

  return (
    <EditorPage
      title={trans('permissions')}
      help={trans('permissions_help')}
      managerOnly={true}
      definition={[
        {
          name: 'public',
          title: trans('public_sequence', {}, 'evaluation'),
          primary: true,
          fields: [
            {
              name: 'meta.public',
              type: 'boolean',
              label: trans('make_sequence_public', {}, 'evaluation'),
              help: trans('make_sequence_public_help', {}, 'evaluation')
            }
          ]
        }, {
          name: 'participants',
          title: trans('Participants'),
          description: trans('Définissez les utilisateurs pouvant participer à cette séquence.'),
          primary: true,
          component: Assignments
        }, {
          name: 'restrictions',
          icon: 'fa fa-fw fa-key',
          title: trans('access_restrictions'),
          description: trans('Ajoutez des conditions d\'accès supplémentaires à vos contenus. Les utilisateurs ayant la permission "Administrer" ne sont pas affectés.'),
          primary: true,
          fields: [
            {
              name: 'restrictions.enableDates',
              label: trans('restrict_by_dates'),
              help: trans('restrict_by_dates_help'),
              type: 'boolean',
              calculated: restrictedByDates,
              onChange: activated => {
                if (!activated) {
                  updateProp('restrictions.dates', [])
                }
              },
              linked: [
                {
                  name: 'restrictions.dates',
                  type: 'date-range',
                  label: trans('access_dates'),
                  displayed: restrictedByDates,
                  required: true,
                  options: {
                    time: true
                  }
                }
              ]
            }, {
              name: 'restrictions.enableCode',
              label: trans('restrict_by_code'),
              help: trans('restrict_by_code_help'),
              type: 'boolean',
              calculated: restrictByCode,
              onChange: activated => {
                if (!activated) {
                  updateProp('restrictions.code', '')
                }
              },
              linked: [
                {
                  name: 'restrictions.code',
                  label: trans('access_code'),
                  displayed: restrictByCode,
                  type: 'password',
                  required: true,
                  autoComplete: 'off',
                  options: {
                    disablePasswordCheck: true
                  }
                }
              ]
            }
          ]
        }
      ]}
    />
  )
}

export {
  SequenceEditorPermissions
}
