import React from 'react'
import {PropTypes as T} from 'prop-types'
import classes from 'classnames'

// TODO : use custom components instead
import Tab from 'react-bootstrap/lib/Tab'
import Tabs from 'react-bootstrap/lib/Tabs'

import {param} from '#/main/app/config'
import {trans}  from '#/main/app/intl/translation'
import {CallbackButton} from '#/main/app/buttons/callback'

import {ContentRights} from '#/main/app/content/components/rights'
import {
  getSimpleAccessRule,
  setSimpleAccessRule,
  hasCustomRules
} from '#/main/core/resource/permissions'

const SimpleAccessRule = props =>
  <CallbackButton
    className={classes('simple-right', {
      selected: props.mode === props.currentMode
    })}
    callback={() => props.toggleMode(props.mode)}
  >
    <span className={classes('simple-right-icon', props.icon)} />
    <span className="simple-right-label">{trans('resource_rights_'+props.mode, {}, 'resource')}</span>
  </CallbackButton>

SimpleAccessRule.propTypes = {
  icon: T.string.isRequired,
  mode: T.string.isRequired,
  currentMode: T.string.isRequired,
  toggleMode: T.func.isRequired
}

const SimpleTab = props =>
  <div className="resource-rights-simple">
    <p>{trans('resource_access_rights', {}, 'resource')}</p>

    <div className="resource-rights-simple-group">
      <SimpleAccessRule mode="all" icon="fa fa-globe" {...props} />
      <SimpleAccessRule mode="user" icon="fa fa-users" {...props} />
      <SimpleAccessRule mode="workspace" icon="fa fa-book" {...props} />
      <SimpleAccessRule mode="admin" icon="fa fa-lock" {...props} />
    </div>

    {props.customRules &&
      <p className="resource-custom-rules-info">
        <span className="fa fa-asterisk" />
        {trans('resource_rights_custom_help', {}, 'resource')}
      </p>
    }
  </div>

SimpleTab.propTypes = {
  currentMode: T.string,
  customRules: T.bool,
  toggleMode: T.func.isRequired
}

SimpleTab.defaultProps = {
  currentMode: '',
  customRules: false
}

const ResourceRights = props =>
  <Tabs id={`${props.resourceNode.id}-tabs`} defaultActiveKey="simple">
    <Tab eventKey="simple" title={trans('simple')}>
      <SimpleTab
        currentMode={getSimpleAccessRule(props.resourceNode.rights, props.resourceNode.workspace)}
        customRules={hasCustomRules(props.resourceNode.rights, props.resourceNode.workspace)}
        toggleMode={(mode) => props.updateRights(
          setSimpleAccessRule(props.resourceNode.rights, mode, props.resourceNode.workspace)
        )}
      />
    </Tab>

    <Tab eventKey="advanced" title={trans('advanced')}>
      <ContentRights
        workspace={props.resourceNode.workspace}
        creatable={param('resources.types').reduce((resourceTypes, current) => Object.assign(resourceTypes, {
          [current.name]: trans(current.name, {}, 'resource')
        }), {})}
        rights={props.resourceNode.rights}
        updateRights={props.updateRights}
      />
    </Tab>
  </Tabs>

ResourceRights.propTypes = {
  resourceNode: T.shape({
    id: T.string.isRequired, // will not properly work in creation
    workspace: T.shape({
      id: T.string.isRequired
    }),
    rights: T.arrayOf(T.shape({
      name: T.string.isRequired,
      translationKey: T.string.isRequired,
      permissions: T.object.isRequired
    })).isRequired
  }).isRequired,
  updateRights: T.func.isRequired
}

export {
  ResourceRights
}
