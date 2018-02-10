import React, {Component} from 'react'
import {connect} from 'react-redux'
import {PropTypes as T} from 'prop-types'
import classes from 'classnames'
import Panel from 'react-bootstrap/lib/Panel'
import PanelGroup from 'react-bootstrap/lib/PanelGroup'

import {trans, t} from '#/main/core/translation'

import {FormGroup} from '#/main/core/layout/form/components/group/form-group.jsx'
import {CheckGroup} from '#/main/core/layout/form/components/group/check-group.jsx'
import {SelectGroup} from '#/main/core/layout/form/components/group/select-group.jsx'
import {RadiosGroup} from '#/main/core/layout/form/components/group/radios-group.jsx'
import {Date} from '#/main/core/layout/form/components/field/date.jsx'

import {select as resourceSelect} from '#/main/core/resource/selectors'
import {actions as modalActions} from '#/main/core/layout/modal/actions'
import {constants as listConstants} from '#/main/core/data/list/constants'
import {actions} from '../actions'

const getMultipleSelectValues = (e) => {
  const values = []

  for (let i = 0; i < e.target.options.length; i++) {
    if (e.target.options[i].selected) {
      values.push(e.target.options[i].value)
    }
  }

  return values
}

const General = props =>
  <fieldset>
    <FormGroup
      id="params-max-entries"
      label={trans('label_max_entries', {}, 'clacoform')}
    >
      <input
        id="params-max-entries"
        type="number"
        min="0"
        value={props.params.max_entries}
        className="form-control"
        onChange={e => props.updateParameters('max_entries', parseInt(e.target.value))}
      />
    </FormGroup>
    <CheckGroup
      id="params-creation-enabled"
      value={props.params.creation_enabled}
      label={trans('label_creation_enabled', {}, 'clacoform')}
      onChange={checked => props.updateParameters('creation_enabled', checked)}
    />
    <CheckGroup
      id="params-edition-enabled"
      value={props.params.edition_enabled}
      label={trans('label_edition_enabled', {}, 'clacoform')}
      onChange={checked => props.updateParameters('edition_enabled', checked)}
    />
    <CheckGroup
      id="params-moderated"
      value={props.params.moderated}
      label={trans('label_moderated', {}, 'clacoform')}
      onChange={checked => props.updateParameters('moderated', checked)}
    />
  </fieldset>

General.propTypes = {
  params: T.shape({
    max_entries: T.number,
    creation_enabled: T.boolean,
    edition_enabled: T.boolean,
    moderated: T.boolean
  }).isRequired,
  updateParameters: T.func.isRequired
}

const Display = props =>
  <fieldset>
    <RadiosGroup
      id="params-default-home"
      label={trans('label_default_home', {}, 'clacoform')}
      options={[
        {value: 'menu', label: trans('menu', {}, 'clacoform')},
        {value: 'random', label: trans('random_mode', {}, 'clacoform')},
        {value: 'search', label: trans('search_mode', {}, 'clacoform')},
        {value: 'add', label: trans('entry_addition', {}, 'clacoform')}
      ]}
      value={props.params.default_home}
      onChange={value => props.updateParameters('default_home', value)}
    />
    <RadiosGroup
      id="params-display-nb-entries"
      label={trans('label_display_nb_entries', {}, 'clacoform')}
      options={[
        {value: 'all', label: trans('choice_entry_all', {}, 'clacoform')},
        {value: 'published', label: trans('choice_entry_published', {}, 'clacoform')},
        {value: 'none', label: t('no')}
      ]}
      value={props.params.display_nb_entries}
      onChange={value => props.updateParameters('display_nb_entries', value)}
    />
    <RadiosGroup
      id="params-menu-position"
      label={trans('label_menu_position', {}, 'clacoform')}
      options={[
        {value: 'down', label: trans('choice_menu_position_down', {}, 'clacoform')},
        {value: 'up', label: trans('choice_menu_position_up', {}, 'clacoform')},
        {value: 'both', label: trans('both', {}, 'clacoform')}
      ]}
      value={props.params.menu_position}
      onChange={value => props.updateParameters('menu_position', value)}
    />
  </fieldset>

Display.propTypes = {
  params: T.shape({
    default_home: T.string,
    display_nb_entries: T.string,
    menu_position: T.string
  }).isRequired,
  updateParameters: T.func.isRequired
}

const Random = props =>
  <fieldset>
    <CheckGroup
      id="params-random-enabled"
      value={props.params.random_enabled}
      label={trans('label_random_enabled', {}, 'clacoform')}
      onChange={checked => props.updateParameters('random_enabled', checked)}
    />
    <div className="form-group row">
      <span className="control-label col-md-4">
        {trans('label_random_categories', {}, 'clacoform')}
      </span>
      <div className="col-md-5">
        <select
          className="form-control"
          name="params-random-categories[]"
          defaultValue={props.params.random_categories}
          onChange={e => props.updateParameters('random_categories', getMultipleSelectValues(e))}
          multiple
        >
          {props.categories.map(category =>
            <option key={category.id} value={category.id}>
              {category.name}
            </option>
          )}
        </select>
      </div>
    </div>
    <div className="form-group form-group-align row">
      <span className="control-label col-md-4">
        {trans('label_random_date', {}, 'clacoform')}
      </span>
      <div className="col-md-2">
        <Date
          id="params-random-start-date"
          value={props.params.random_start_date}
          onChange={date => props.updateParameters('random_start_date', date)}
        />
      </div>
      <div className="col-md-1 text-center">
        <i className="fa fa-fw fa-long-arrow-right"></i>
      </div>
      <div className="col-md-2">
        <Date
          id="params-random-end-date"
          value={props.params.random_end_date}
          onChange={date => props.updateParameters('random_end_date', date)}
        />
      </div>
    </div>
  </fieldset>

Random.propTypes = {
  params: T.shape({
    random_enabled: T.boolean,
    random_categories: T.array,
    random_start_date: T.string,
    random_end_date: T.string
  }).isRequired,
  categories: T.arrayOf(T.shape({
    id: T.number.isRequired,
    name: T.string.isRequired
  })),
  updateParameters: T.func.isRequired
}

const List = props =>
  <fieldset>
    <CheckGroup
      id="params-search-enabled"
      value={props.params.search_enabled}
      label={trans('label_search_enabled', {}, 'clacoform')}
      onChange={checked => props.updateParameters('search_enabled', checked)}
    />
    <CheckGroup
      id="params-search-column-enabled"
      value={props.params.search_column_enabled}
      label={trans('label_search_column_enabled', {}, 'clacoform')}
      onChange={checked => props.updateParameters('search_column_enabled', checked)}
    />
    <div className="form-group row">
      <span className="control-label col-md-3">
        {trans('label_search_columns', {}, 'clacoform')}
      </span>
      <div className="col-md-5">
        <select
          className="form-control"
          name="params-search-colums[]"
          defaultValue={props.params.search_columns}
          onChange={e => props.updateParameters('search_columns', getMultipleSelectValues(e))}
          multiple
        >
          <option value="title">
            {t('title')}
          </option>
          <option value="creationDateString">
            {t('date')}
          </option>
          <option value="userString">
            {t('user')}
          </option>
          <option value="categoriesString">
            {t('categories')}
          </option>
          <option value="keywordsString">
            {trans('keywords', {}, 'clacoform')}
          </option>
          {props.fields.filter(f => !f.hidden).map(field =>
            <option key={field.id} value={field.id}>
              {field.name}
            </option>
          )}
        </select>
      </div>
    </div>
    <RadiosGroup
      id="params-default-display-mode"
      label={trans('default_display_mode', {}, 'clacoform')}
      options={
        Object.keys(listConstants.DISPLAY_MODES).map(key => {
          return {
            value: key,
            label: listConstants.DISPLAY_MODES[key].label
          }
        })
      }
      value={props.params.default_display_mode || listConstants.DISPLAY_TABLE}
      onChange={value => props.updateParameters('default_display_mode', value)}
    />
    <SelectGroup
      id="params-display-title"
      label={trans('field_for_title', {}, 'clacoform')}
      choices={generateDisplayList(props)}
      noEmpty={true}
      value={props.params.display_title || 'title'}
      onChange={value => props.updateParameters('display_title', value)}
    />
    <SelectGroup
      id="params-display-subtitle"
      label={trans('field_for_subtitle', {}, 'clacoform')}
      choices={generateDisplayList(props)}
      noEmpty={true}
      value={props.params.display_subtitle || 'title'}
      onChange={value => props.updateParameters('display_subtitle', value)}
    />
    <SelectGroup
      id="params-display-content"
      label={trans('field_for_content', {}, 'clacoform')}
      choices={generateDisplayList(props)}
      noEmpty={true}
      value={props.params.display_content || 'title'}
      onChange={value => props.updateParameters('display_content', value)}
    />
  </fieldset>

List.propTypes = {
  params: T.shape({
    search_enabled: T.boolean,
    search_column_enabled: T.boolean,
    search_columns: T.array,
    default_display_mode: T.string,
    display_title: T.string,
    display_subtitle: T.string,
    display_content: T.string
  }).isRequired,
  fields: T.arrayOf(T.shape({
    id: T.number.isRequired,
    name: T.string.isRequired
  })),
  updateParameters: T.func.isRequired
}

const Metadata = props =>
  <fieldset>
    <RadiosGroup
      id="params-display-metadata"
      label={trans('label_display_metadata', {}, 'clacoform')}
      options={[
        {value: 'all', label: t('yes')},
        {value: 'none', label: t('no')},
        {value: 'manager', label: trans('choice_manager_only', {}, 'clacoform')}
      ]}
      value={props.params.display_metadata}
      onChange={value => props.updateParameters('display_metadata', value)}
    />
  </fieldset>

Metadata.propTypes = {
  params: T.shape({
    display_metadata: T.string
  }).isRequired,
  updateParameters: T.func.isRequired
}

const Locked = props =>
  <fieldset>
    <RadiosGroup
      id="params-locked-fields-for"
      label={trans('lock_fields', {}, 'clacoform')}
      options={[
        {value: 'user', label: trans('choice_user_only', {}, 'clacoform')},
        {value: 'manager', label: trans('choice_manager_only', {}, 'clacoform')},
        {value: 'all', label: trans('both', {}, 'clacoform')}
      ]}
      value={props.params.locked_fields_for}
      onChange={value => props.updateParameters('locked_fields_for', value)}
    />
  </fieldset>

Locked.propTypes = {
  params: T.shape({
    locked_fields_for: T.string
  }).isRequired,
  updateParameters: T.func.isRequired
}

const Categories = props =>
  <fieldset>
    <CheckGroup
      id="params-display-categories"
      value={props.params.display_categories}
      label={trans('label_display_categories', {}, 'clacoform')}
      onChange={checked => props.updateParameters('display_categories', checked)}
    />
  </fieldset>

Categories.propTypes = {
  params: T.shape({
    display_categories: T.boolean
  }).isRequired,
  updateParameters: T.func.isRequired
}

const Comments = props =>
  <fieldset>
    <CheckGroup
      id="params-comments-enabled"
      value={props.params.comments_enabled}
      label={trans('label_comments_enabled', {}, 'clacoform')}
      onChange={checked => props.updateParameters('comments_enabled', checked)}
    />

    {props.params.comments_enabled &&
      <SelectGroup
        id="params-comments-roles"
        label={trans('enable_comments_for_roles', {}, 'clacoform')}
        choices={props.roles.reduce((acc, r) => {
          acc[r.name] = t(r.translationKey)

          return acc
        }, {})}
        multiple={true}
        value={props.params.comments_roles || []}
        onChange={value => props.updateParameters('comments_roles', value)}
      />
    }

    <RadiosGroup
      id="params-moderate-comments"
      label={trans('label_moderate_comments', {}, 'clacoform')}
      options={[
        {value: 'all', label: t('yes')},
        {value: 'none', label: t('no')},
        {value: 'anonymous', label: trans('choice_anonymous_comments_only', {}, 'clacoform')}
      ]}
      value={props.params.moderate_comments}
      onChange={value => props.updateParameters('moderate_comments', value)}
    />
    <CheckGroup
      id="params-display-comments"
      value={props.params.display_comments}
      label={trans('label_display_comments', {}, 'clacoform')}
      onChange={checked => props.updateParameters('display_comments', checked)}
    />
    {props.params.display_comments &&
      <SelectGroup
        id="params-comments-display-roles"
        label={trans('display_comments_for_roles', {}, 'clacoform')}
        choices={props.roles.reduce((acc, r) => {
          acc[r.name] = t(r.translationKey)

          return acc
        }, {})}
        multiple={true}
        value={props.params.comments_display_roles || []}
        onChange={value => props.updateParameters('comments_display_roles', value)}
      />
    }
    <CheckGroup
      id="params-open-comments"
      value={props.params.open_comments}
      label={trans('label_open_panel_by_default', {}, 'clacoform')}
      onChange={checked => props.updateParameters('open_comments', checked)}
    />
    <CheckGroup
      id="params-display-comment-author"
      value={props.params.display_comment_author}
      label={trans('label_display_comment_author', {}, 'clacoform')}
      onChange={checked => props.updateParameters('display_comment_author', checked)}
    />
    <CheckGroup
      id="params-display-comment-date"
      value={props.params.display_comment_date}
      label={trans('label_display_comment_date', {}, 'clacoform')}
      onChange={checked => props.updateParameters('display_comment_date', checked)}
    />
  </fieldset>

Comments.propTypes = {
  params: T.shape({
    comments_enabled: T.boolean,
    anonymous_comments_enabled: T.boolean,
    moderate_comments: T.string,
    display_comments: T.boolean,
    open_comments: T.boolean,
    display_comment_author: T.boolean,
    display_comment_date: T.boolean,
    comments_roles: T.array,
    comments_display_roles: T.array
  }).isRequired,
  roles: T.arrayOf(T.shape({
    name: T.string.isRequired,
    translationKey: T.string.isRequired
  })).isRequired,
  updateParameters: T.func.isRequired
}

const Keywords = props =>
  <fieldset>
    <CheckGroup
      id="params-keywords-enabled"
      value={props.params.keywords_enabled}
      label={trans('label_keywords_enabled', {}, 'clacoform')}
      onChange={checked => props.updateParameters('keywords_enabled', checked)}
    />
    <CheckGroup
      id="params-new-keywords-enabled"
      value={props.params.new_keywords_enabled}
      label={trans('label_new_keywords_enabled', {}, 'clacoform')}
      onChange={checked => props.updateParameters('new_keywords_enabled', checked)}
    />
    <CheckGroup
      id="params-display-keywords"
      value={props.params.display_keywords}
      label={trans('label_display_keywords', {}, 'clacoform')}
      onChange={checked => props.updateParameters('display_keywords', checked)}
    />
  </fieldset>

Keywords.propTypes = {
  params: T.shape({
    keywords_enabled: T.boolean,
    new_keywords_enabled: T.boolean,
    display_keywords: T.boolean
  }).isRequired,
  updateParameters: T.func.isRequired
}

const generateDisplayList = (props) => {
  const displayList = {
    title: t('title'),
    date: t('date'),
    user: t('user'),
    categories: t('categories'),
    keywords: trans('keywords', {}, 'clacoform')
  }

  props.fields.filter(f => !f.hidden).map(field => {
    displayList[field.id] = field.name
  })

  return displayList
}

generateDisplayList.propTypes = {
  fields: T.arrayOf(T.shape({
    id: T.number.isRequired,
    name: T.string.isRequired,
    hidden: T.bool
  }))
}

function makePanel(Section, title, key, props, withCategories = false, withFields = false, withRoles = false) {
  const caretIcon = key === props.params.activePanelKey ? 'fa-caret-down' : 'fa-caret-right'
  const keyValue = key === props.params.activePanelKey ? '' : key

  const Header =
    <div className="editor-panel-title"
         onClick={() => props.updateParameters('activePanelKey', keyValue)}
    >
      <span className={classes('fa fa-fw', caretIcon)}/>
      &nbsp;{title}
    </div>

  return (
    <Panel
      eventKey={key}
      header={Header}
    >
      <Section
        updateParameters={props.updateParameters}
        params={props.params}
        categories={withCategories ? props.categories : []}
        fields={withFields ? props.fields : []}
        roles={withRoles ? props.roles : []}
      />
    </Panel>
  )
}

makePanel.propTypes = {
  params: T.shape({
    max_entries: T.number,
    creation_enabled: T.boolean,
    edition_enabled: T.boolean,
    moderated: T.boolean,
    default_home: T.string,
    display_nb_entries: T.string,
    menu_position: T.string,
    random_enabled: T.boolean,
    random_categories: T.array,
    random_start_date: T.string,
    random_end_date: T.string,
    search_enabled: T.boolean,
    search_column_enabled: T.boolean,
    search_columns: T.array,
    display_metadata: T.string,
    locked_fields_for: T.string,
    display_categories: T.boolean,
    comments_enabled: T.boolean,
    anonymous_comments_enabled: T.boolean,
    moderate_comments: T.string,
    display_comments: T.boolean,
    open_comments: T.boolean,
    display_comment_author: T.boolean,
    display_comment_date: T.boolean,
    votes_enabled: T.boolean,
    display_votes: T.boolean,
    open_votes: T.boolean,
    keywords_enabled: T.boolean,
    new_keywords_enabled: T.boolean,
    display_keywords: T.boolean,
    activePanelKey: T.string
  }).isRequired,
  categories: T.arrayOf(T.shape({
    id: T.number.isRequired,
    name: T.string.isRequired
  })),
  fields: T.arrayOf(T.shape({
    id: T.number.isRequired,
    name: T.string.isRequired,
    hidden: T.bool
  })),
  roles: T.array,
  updateParameters: T.func.isRequired
}

class ClacoFormConfig extends Component {
  componentDidMount() {
    this.props.initializeParameters()
  }

  render() {
    return (
      <div>
        <h2 className="h-first">
          {trans('general_configuration', {}, 'clacoform')}
        </h2>
        {this.props.canEdit ?
          <form>
            <PanelGroup accordion>
              {makePanel(General, t('general'), 'general', this.props)}
              {makePanel(Display, t('display_parameters', {}, 'clacoform'), 'display', this.props)}
              {makePanel(Random, trans('random_entries', {}, 'clacoform'), 'random_entries', this.props, true)}
              {makePanel(List, trans('entries_list_search', {}, 'clacoform'), 'entries_list_search', this.props, false, true)}
              {makePanel(Metadata, trans('metadata', {}, 'clacoform'), 'metadata', this.props)}
              {makePanel(Locked, trans('locked_fields', {}, 'clacoform'), 'locked_fields', this.props)}
              {makePanel(Categories, t('categories'), 'categories', this.props)}
              {makePanel(Comments, trans('comments', {}, 'clacoform'), 'comments', this.props, false, false, true)}
              {makePanel(Keywords, trans('keywords', {}, 'clacoform'), 'keywords', this.props)}
            </PanelGroup>
          </form> :
          <div className="alert alert-danger">
            {t('unauthorized')}
          </div>
        }
      </div>
    )
  }
}

ClacoFormConfig.propTypes = {
  canEdit: T.bool.isRequired,
  params: T.shape({
    max_entries: T.number,
    creation_enabled: T.boolean,
    edition_enabled: T.boolean,
    moderated: T.boolean,
    default_home: T.string,
    display_nb_entries: T.string,
    menu_position: T.string,
    random_enabled: T.boolean,
    random_categories: T.array,
    random_start_date: T.string,
    random_end_date: T.string,
    search_enabled: T.boolean,
    search_column_enabled: T.boolean,
    search_columns: T.array,
    display_metadata: T.string,
    locked_fields_for: T.string,
    display_categories: T.boolean,
    comments_enabled: T.boolean,
    anonymous_comments_enabled: T.boolean,
    moderate_comments: T.string,
    display_comments: T.boolean,
    open_comments: T.boolean,
    display_comment_author: T.boolean,
    display_comment_date: T.boolean,
    votes_enabled: T.boolean,
    display_votes: T.boolean,
    open_votes: T.boolean,
    keywords_enabled: T.boolean,
    new_keywords_enabled: T.boolean,
    display_keywords: T.boolean,
    use_template: T.boolean,
    activePanelKey: T.string
  }),
  categories: T.arrayOf(T.shape({
    id: T.number.isRequired,
    name: T.string.isRequired
  })),
  fields: T.arrayOf(T.shape({
    id: T.number.isRequired,
    name: T.string.isRequired,
    hidden: T.bool
  })),
  roles: T.array,
  initializeParameters: T.func.isRequired,
  updateParameters: T.func.isRequired,
  deleteAllEntries: T.func.isRequired,
  showModal: T.func.isRequired
}

function mapStateToProps(state) {
  return {
    canEdit: resourceSelect.editable(state),
    params: state.parameters,
    categories: state.categories,
    fields: state.fields,
    roles: state.roles
  }
}

function mapDispatchToProps(dispatch) {
  return {
    initializeParameters: () => dispatch(actions.initializeParameters()),
    updateParameters: (property, value) => dispatch(actions.updateParameters(property, value)),
    deleteAllEntries: () => dispatch(actions.deleteAllEntries()),
    showModal: (type, props) => dispatch(modalActions.showModal(type, props))
  }
}

const ConnectedClacoFormConfig = connect(mapStateToProps, mapDispatchToProps)(ClacoFormConfig)

export {ConnectedClacoFormConfig as ClacoFormConfig}