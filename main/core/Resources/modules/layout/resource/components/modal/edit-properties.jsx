import React, {Component} from 'react'
import {PropTypes as T} from 'prop-types'
import get from 'lodash/get'
import set from 'lodash/set'
import isEmpty from 'lodash/isEmpty'
import cloneDeep from 'lodash/cloneDeep'
import omit from 'lodash/omit'

import Modal      from 'react-bootstrap/lib/Modal'

import {t}            from '#/main/core/translation'
import {t_res}        from '#/main/core/layout/resource/translation'
import {BaseModal}    from '#/main/core/layout/modal/components/base.jsx'
import {FormSections, FormSection} from '#/main/core/layout/form/components/form-sections.jsx'
import {ActivableSet} from '#/main/core/layout/form/components/fieldset/activable-set.jsx'
import {FormGroup}    from '#/main/core/layout/form/components/group/form-group.jsx'
import {HtmlGroup}    from '#/main/core/layout/form/components/group/html-group.jsx'
import {CheckGroup}   from '#/main/core/layout/form/components/group/check-group.jsx'
import {TextGroup}    from '#/main/core/layout/form/components/group/text-group.jsx'
import {DatePicker}   from '#/main/core/layout/form/components/field/date-picker.jsx'
import {IpList}       from '#/main/core/layout/form/components/field/ip-list.jsx'
import {validate}     from '#/main/core/layout/resource/validator'
import {closeTargets} from '#/main/core/layout/resource/enums'

export const MODAL_RESOURCE_PROPERTIES = 'MODAL_RESOURCE_PROPERTIES'

const MetaSection = props =>
  <FormSection
    id="resource-meta"
    icon="fa fa-fw fa-info"
    title={t_res('resource_meta')}
    {...omit(props, ['meta', 'updateParameter'])}
  >
    <HtmlGroup
      controlId="resource-description"
      label={t_res('resource_description')}
      content={props.meta.description || ''}
      onChange={description => props.updateParameter('meta.description', description)}
    />

    <CheckGroup
      checkId="resource-published"
      label={t_res('resource_not_published')}
      labelChecked={t_res('resource_published')}
      checked={props.meta.published}
      onChange={checked => props.updateParameter('meta.published', checked)}
    />

    <CheckGroup
      checkId="resource-portal"
      label={t_res('resource_portal_not_published')}
      labelChecked={t_res('resource_portal_published')}
      checked={props.meta.portal}
      onChange={checked => props.updateParameter('meta.portal', checked)}
      help={t_res('resource_portal_help')}
    />
  </FormSection>

MetaSection.propTypes = {
  meta: T.shape({
    description: T.string,
    published: T.bool.isRequired,
    portal: T.bool.isRequired
  }).isRequired,
  updateParameter: T.func.isRequired,
  validating: T.bool.isRequired,
  errors: T.object
}

const DisplaySection = props =>
  <FormSection
    id="resource-display"
    icon="fa fa-fw fa-desktop"
    title={t('display_parameters')}
    {...omit(props, ['parameters', 'updateParameter'])}
  >
    <CheckGroup
      checkId="resource-fullscreen"
      label={t_res('resource_fullscreen')}
      checked={props.parameters.fullscreen}
      onChange={checked => props.updateParameter('parameters.fullscreen', checked)}
    />

    <CheckGroup
      checkId="resource-closable"
      label={t_res('resource_closable')}
      checked={props.parameters.closable}
      onChange={checked => props.updateParameter('parameters.closable', checked)}
    />

    <FormGroup
      controlId="resource-close-target"
      label={t_res('resource_close_target')}
      warnOnly={!props.validating}
      error={get(props, 'errors.parameters.closeTarget')}
    >
      <select
        id="resource-close-target"
        value={props.parameters.closeTarget}
        className="form-control"
        onChange={(e) => props.updateParameter('parameters.closeTarget', e.target.value)}
      >
        {closeTargets.map(target =>
          <option key={target[0]} value={target[0]}>{t_res(target[1])}</option>
        )}
      </select>
    </FormGroup>
  </FormSection>

DisplaySection.propTypes = {
  parameters: T.shape({
    fullscreen: T.bool.isRequired,
    closable: T.bool.isRequired,
    closeTarget: T.number.isRequired
  }).isRequired,
  updateParameter: T.func.isRequired,
  validating: T.bool,
  errors: T.object
}

const AccessesSection = (props) =>
  <FormSection
    id="resource-accesses"
    icon="fa fa-fw fa-key"
    title={t('access_restrictions')}
    {...omit(props, ['meta', 'parameters', 'updateParameter'])}
  >
    <ActivableSet
      id="access-dates"
      label={t('access_dates')}
      activated={!isEmpty(props.parameters.accessibleFrom) || !isEmpty(props.parameters.accessibleUntil)}
      onChange={activated => {
        if (!activated) {
          props.updateParameter('parameters.accessibleFrom', null)
          props.updateParameter('parameters.accessibleUntil', null)
        }
      }}
    >
      <div className="row">
        <FormGroup
          className="col-md-6 col-xs-6 form-last"
          controlId="resource-accessible-from"
          label={t_res('resource_accessible_from')}
          validating={props.validating}
        >
          <DatePicker
            id="resource-accessible-from"
            value={props.parameters.accessibleFrom || ''}
            onChange={date => props.updateParameter('parameters.accessibleFrom', date)}
          />
        </FormGroup>

        <FormGroup
          className="col-md-6 col-xs-6 form-last"
          controlId="resource-accessible-until"
          label={t_res('resource_accessible_until')}
          validating={props.validating}
        >
          <DatePicker
            id="resource-accessible-until"
            value={props.parameters.accessibleUntil || ''}
            onChange={date => props.updateParameter('parameters.accessibleUntil', date)}
          />
        </FormGroup>
      </div>
    </ActivableSet>

    <ActivableSet
      id="access-code"
      label={t_res('resource_access_code')}
      activated={!isEmpty(props.meta.accesses.code)}
      onChange={activated => {
        if (!activated) {
          props.updateParameter('meta.accesses.code', null)
        }
      }}
    >
      <FormGroup
        controlId="resource-access-code"
        label={t('access_code')}
        validating={props.validating}
      >
        <input
          id="resource-access-code"
          type="password"
          className="form-control"
          value={props.meta.accesses.code || ''}
          onChange={e => props.updateParameter('meta.accesses.code', e.target.value)}
        />
      </FormGroup>
    </ActivableSet>

    <ActivableSet
      id="access-ips"
      label={t_res('resource_access_ips')}
      activated={props.meta.accesses.ip.activateFilters}
      onChange={activated => {
        props.updateParameter('meta.accesses.ip.activateFilters', activated)
        if (!activated) {
          props.updateParameter('meta.accesses.ip.ips', [])
        }
      }}
    >
      <IpList
        id="resource-access-ips"
        ips={props.meta.accesses.ip.ips}
        onChange={ips => props.updateParameter('meta.accesses.ip.ips', ips)}
        emptyText={t_res('resource_no_allowed_ip')}
      />
    </ActivableSet>
  </FormSection>

AccessesSection.propTypes = {
  meta: T.shape({
    accesses: T.shape({
      ip: T.shape({
        ips: T.array,
        activateFilters: T.bool
      }),
      code: T.string
    })
  }).isRequired,
  parameters: T.shape({
    accessibleFrom: T.string,
    accessibleUntil: T.string
  }).isRequired,
  updateParameter: T.func.isRequired,
  validating: T.bool.isRequired,
  errors: T.object
}

const LicenseSection = props =>
  <FormSection
    id="resource-license"
    icon="fa fa-fw fa-copyright"
    title={t_res('resource_authors_license')}
    {...omit(props, ['meta', 'updateParameter'])}
  >
    <TextGroup
      controlId="resource-authors"
      label={t_res('resource_authors')}
      value={props.meta.authors}
      onChange={text => props.updateParameter('meta.authors', text)}
    />

    <TextGroup
      controlId="resource-license"
      label={t_res('resource_license')}
      value={props.meta.license}
      onChange={text => props.updateParameter('meta.license', text)}
    />
  </FormSection>

LicenseSection.propTypes = {
  meta: T.shape({
    authors: T.string,
    license: T.string
  }).isRequired,
  updateParameter: T.func.isRequired,
  validating: T.bool.isRequired,
  errors: T.object
}

class EditPropertiesModal extends Component {
  constructor(props) {
    super(props)

    this.state = {
      resourceNode: cloneDeep(props.resourceNode),
      pendingChanges: false,
      validating: false,
      errors: {}
    }

    this.save = this.save.bind(this)
    this.updateProperty = this.updateProperty.bind(this)
  }

  /**
   * Updates a property in the resource node.
   *
   * @param {string} parameter - the path of the parameter in the node (eg. 'meta.published')
   * @param value
   */
  updateProperty(parameter, value) {
    // Update state and validate new resourceNode data
    this.setState((prevState) => {
      const newNode = cloneDeep(prevState.resourceNode)
      set(newNode, parameter, value)

      return {
        resourceNode: newNode,
        pendingChanges: true,
        validating: false,
        errors: validate(newNode)
      }
    })
  }

  /**
   * Saves the resource node updates if valid.
   */
  save() {
    const errors = validate(this.state.resourceNode)

    this.setState({
      validating: true,
      errors: errors
    })

    if (isEmpty(errors)) {
      this.props.save(this.state.resourceNode)
      this.props.fadeModal()
    }
  }

  render() {
    return (
      <BaseModal
        icon="fa fa-fw fa-pencil"
        title={t_res('edit-properties')}
        className="resource-edit-properties-modal"
        {...this.props}
      >
        <Modal.Body>
          <TextGroup
            controlId="resource-name"
            label={t_res('resource_name')}
            value={this.state.resourceNode.name}
            onChange={text => this.updateProperty('name', text)}
            warnOnly={!this.state.validating}
            error={get(this.state, 'errors.name')}
          />
        </Modal.Body>

        <FormSections>
          <MetaSection
            meta={this.state.resourceNode.meta}
            updateParameter={this.updateProperty}
            validating={this.state.validating}
            errors={this.state.errors}
          />

          <DisplaySection
            parameters={this.state.resourceNode.parameters}
            updateParameter={this.updateProperty}
            validating={this.state.validating}
            errors={this.state.errors}
          />

          <AccessesSection
            meta={this.state.resourceNode.meta}
            parameters={this.state.resourceNode.parameters}
            updateParameter={this.updateProperty}
            validating={this.state.validating}
            errors={this.state.errors}
          />

          <LicenseSection
            meta={this.state.resourceNode.meta}
            updateParameter={this.updateProperty}
            validating={this.state.validating}
            errors={this.state.errors}
          />
        </FormSections>

        <button
          className="modal-btn btn btn-primary"
          disabled={!this.state.pendingChanges || (this.state.validating && !isEmpty(this.state.errors))}
          onClick={this.save}
        >
          {t('save')}
        </button>
      </BaseModal>
    )
  }
}

EditPropertiesModal.propTypes = {
  resourceNode: T.shape({
    name: T.string.isRequired,
    parameters: T.object.isRequired,
    meta: T.object.isRequired
  }),
  fadeModal: T.func.isRequired,
  save: T.func.isRequired
}

export {
  EditPropertiesModal
}
