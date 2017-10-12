import React, {Component} from 'react'
import {connect} from 'react-redux'
import {PropTypes as T} from 'prop-types'
import classes from 'classnames'
import moment from 'moment'
import Panel from 'react-bootstrap/lib/Panel'
import PanelGroup from 'react-bootstrap/lib/PanelGroup'

import {trans, t} from '#/main/core/translation'

import {FormGroup} from '#/main/core/layout/form/components/group/form-group.jsx'
import {CheckGroup} from '#/main/core/layout/form/components/group/check-group.jsx'
import {SelectGroup} from '#/main/core/layout/form/components/group/select-group.jsx'
import {RadioGroup} from '#/main/core/layout/form/components/group/radio-group.jsx'
import {DatePicker} from '#/main/core/layout/form/components/field/date-picker.jsx'


import {select as resourceSelect} from '#/main/core/layout/resource/selectors'
import {actions as modalActions} from '#/main/core/layout/modal/actions'
import {MODAL_DELETE_CONFIRM} from '#/main/core/layout/modal'
import {constants as listConstants} from '#/main/core/layout/list/constants'
import {actions} from '../actions'
import {Message} from '../../components/message.jsx'

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
      controlId="params-max-entries"
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
      checkId="params-creation-enabled"
      checked={props.params.creation_enabled}
      label={trans('label_creation_enabled', {}, 'clacoform')}
      onChange={checked => props.updateParameters('creation_enabled', checked)}
    />
    <CheckGroup
      checkId="params-edition-enabled"
      checked={props.params.edition_enabled}
      label={trans('label_edition_enabled', {}, 'clacoform')}
      onChange={checked => props.updateParameters('edition_enabled', checked)}
    />
    <CheckGroup
      checkId="params-moderated"
      checked={props.params.moderated}
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
    <RadioGroup
      controlId="params-default-home"
      label={trans('label_default_home', {}, 'clacoform')}
      options={[
        {value: 'menu', label: trans('menu', {}, 'clacoform')},
        {value: 'random', label: trans('random_mode', {}, 'clacoform')},
        {value: 'search', label: trans('search_mode', {}, 'clacoform')},
        {value: 'add', label: trans('entry_addition', {}, 'clacoform')}
      ]}
      checkedValue={props.params.default_home}
      onChange={value => props.updateParameters('default_home', value)}
    />
    <RadioGroup
      controlId="params-display-nb-entries"
      label={trans('label_display_nb_entries', {}, 'clacoform')}
      options={[
        {value: 'all', label: trans('choice_entry_all', {}, 'clacoform')},
        {value: 'published', label: trans('choice_entry_published', {}, 'clacoform')},
        {value: 'none', label: t('no')}
      ]}
      checkedValue={props.params.display_nb_entries}
      onChange={value => props.updateParameters('display_nb_entries', value)}
    />
    <RadioGroup
      controlId="params-menu-position"
      label={trans('label_menu_position', {}, 'clacoform')}
      options={[
        {value: 'down', label: trans('choice_menu_position_down', {}, 'clacoform')},
        {value: 'up', label: trans('choice_menu_position_up', {}, 'clacoform')},
        {value: 'both', label: trans('both', {}, 'clacoform')}
      ]}
      checkedValue={props.params.menu_position}
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
      checkId="params-random-enabled"
      checked={props.params.random_enabled}
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
        <DatePicker
          id="params-random-start-date"
          dateFormat="DD/MM/YYYY"
          minDate={moment.utc('1900-01-01T12:00:00')}
          locale="fr"
          value={props.params.random_start_date || ''}
          onChange={date => props.updateParameters('random_start_date', date)}
        />
      </div>
      <div className="col-md-1 text-center">
        <span className="fa fa-w fa-long-arrow-right" />
      </div>
      <div className="col-md-2">
        <DatePicker
          id="params-random-end-date"
          dateFormat="DD/MM/YYYY"
          minDate={moment.utc('1900-01-01T12:00:00')}
          locale="fr"
          value={props.params.random_end_date || ''}
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
      checkId="params-search-enabled"
      checked={props.params.search_enabled}
      label={trans('label_search_enabled', {}, 'clacoform')}
      onChange={checked => props.updateParameters('search_enabled', checked)}
    />
    <CheckGroup
      checkId="params-search-column-enabled"
      checked={props.params.search_column_enabled}
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
    <RadioGroup
      controlId="params-default-display-mode"
      label={trans('default_display_mode', {}, 'clacoform')}
      options={
        Object.keys(listConstants.DISPLAY_MODES).map(key => {
          return {
            value: key,
            label: listConstants.DISPLAY_MODES[key].label
          }
        })
      }
      checkedValue={props.params.default_display_mode || listConstants.DISPLAY_TABLE}
      onChange={value => props.updateParameters('default_display_mode', value)}
    />
    <SelectGroup
      controlId="params-display-title"
      label={trans('field_for_title', {}, 'clacoform')}
      options={generateDisplayList(props)}
      noEmpty={true}
      selectedValue={props.params.display_title || 'title'}
      onChange={value => props.updateParameters('display_title', value)}
    />
    <SelectGroup
      controlId="params-display-subtitle"
      label={trans('field_for_subtitle', {}, 'clacoform')}
      options={generateDisplayList(props)}
      noEmpty={true}
      selectedValue={props.params.display_subtitle || 'title'}
      onChange={value => props.updateParameters('display_subtitle', value)}
    />
    <SelectGroup
      controlId="params-display-content"
      label={trans('field_for_content', {}, 'clacoform')}
      options={generateDisplayList(props)}
      noEmpty={true}
      selectedValue={props.params.display_content || 'title'}
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
    <RadioGroup
      controlId="params-display-metadata"
      label={trans('label_display_metadata', {}, 'clacoform')}
      options={[
        {value: 'all', label: t('yes')},
        {value: 'none', label: t('no')},
        {value: 'manager', label: trans('choice_manager_only', {}, 'clacoform')}
      ]}
      checkedValue={props.params.display_metadata}
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
    <RadioGroup
      controlId="params-locked-fields-for"
      label={trans('lock_fields', {}, 'clacoform')}
      options={[
        {value: 'user', label: trans('choice_user_only', {}, 'clacoform')},
        {value: 'manager', label: trans('choice_manager_only', {}, 'clacoform')},
        {value: 'all', label: trans('both', {}, 'clacoform')}
      ]}
      checkedValue={props.params.locked_fields_for}
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
      checkId="params-display-categories"
      checked={props.params.display_categories}
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
      checkId="params-comments-enabled"
      checked={props.params.comments_enabled}
      label={trans('label_comments_enabled', {}, 'clacoform')}
      onChange={checked => props.updateParameters('comments_enabled', checked)}
    />
    <CheckGroup
      checkId="params-anonymous-comments-enabled"
      checked={props.params.anonymous_comments_enabled}
      label={trans('label_anonymous_comments_enabled', {}, 'clacoform')}
      onChange={checked => props.updateParameters('anonymous_comments_enabled', checked)}
    />
    <RadioGroup
      controlId="params-moderate-comments"
      label={trans('label_moderate_comments', {}, 'clacoform')}
      options={[
        {value: 'all', label: t('yes')},
        {value: 'none', label: t('no')},
        {value: 'anonymous', label: trans('choice_anonymous_comments_only', {}, 'clacoform')}
      ]}
      checkedValue={props.params.moderate_comments}
      onChange={value => props.updateParameters('moderate_comments', value)}
    />
    <CheckGroup
      checkId="params-display-comments"
      checked={props.params.display_comments}
      label={trans('label_display_comments', {}, 'clacoform')}
      onChange={checked => props.updateParameters('display_comments', checked)}
    />
    <CheckGroup
      checkId="params-open-comments"
      checked={props.params.open_comments}
      label={trans('label_open_panel_by_default', {}, 'clacoform')}
      onChange={checked => props.updateParameters('open_comments', checked)}
    />
    <CheckGroup
      checkId="params-display-comment-author"
      checked={props.params.display_comment_author}
      label={trans('label_display_comment_author', {}, 'clacoform')}
      onChange={checked => props.updateParameters('display_comment_author', checked)}
    />
    <CheckGroup
      checkId="params-display-comment-date"
      checked={props.params.display_comment_date}
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
    display_comment_date: T.boolean
  }).isRequired,
  updateParameters: T.func.isRequired
}

const Keywords = props =>
  <fieldset>
    <CheckGroup
      checkId="params-keywords-enabled"
      checked={props.params.keywords_enabled}
      label={trans('label_keywords_enabled', {}, 'clacoform')}
      onChange={checked => props.updateParameters('keywords_enabled', checked)}
    />
    <CheckGroup
      checkId="params-new-keywords-enabled"
      checked={props.params.new_keywords_enabled}
      label={trans('label_new_keywords_enabled', {}, 'clacoform')}
      onChange={checked => props.updateParameters('new_keywords_enabled', checked)}
    />
    <CheckGroup
      checkId="params-display-keywords"
      checked={props.params.display_keywords}
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
  return [
    {value: 'title', label: t('title')},
    {value: 'date', label: t('date')},
    {value: 'user', label: t('user')},
    {value: 'categories', label: t('categories')},
    {value: 'keywords', label: trans('keywords', {}, 'clacoform')}
  ].concat(props.fields.filter(f => !f.hidden).map(field => {
    return {value: field.id, label: field.name}
  }))
}

generateDisplayList.propTypes = {
  fields: T.arrayOf(T.shape({
    id: T.number.isRequired,
    name: T.string.isRequired,
    hidden: T.bool
  }))
}

function makePanel(Section, title, key, props, withCategories = false, withFields = false) {
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
  updateParameters: T.func.isRequired
}

class ClacoFormConfig extends Component {
  componentDidMount() {
    this.props.initializeParameters()
  }

  showAllEntriesDeletion() {
    this.props.showModal(MODAL_DELETE_CONFIRM, {
      title: trans('delete_all_entries', {}, 'clacoform'),
      question: trans('delete_all_entries_confirm_msg', {}, 'clacoform'),
      handleConfirm: () => this.props.deleteAllEntries()
    })
  }

  render() {
    return (
      <div>
        <h2>
          {trans('general_configuration', {}, 'clacoform')}
        </h2>
        <br/>
        {this.props.canEdit ?
          <form>
            <Message/>
            <PanelGroup accordion>
              {makePanel(General, t('general'), 'general', this.props)}
              {makePanel(Display, t('display_parameters', {}, 'clacoform'), 'display', this.props)}
              {makePanel(Random, trans('random_entries', {}, 'clacoform'), 'random_entries', this.props, true)}
              {makePanel(List, trans('entries_list_search', {}, 'clacoform'), 'entries_list_search', this.props, false, true)}
              {makePanel(Metadata, trans('metadata', {}, 'clacoform'), 'metadata', this.props)}
              {makePanel(Locked, trans('locked_fields', {}, 'clacoform'), 'locked_fields', this.props)}
              {makePanel(Categories, t('categories'), 'categories', this.props)}
              {makePanel(Comments, trans('comments', {}, 'clacoform'), 'comments', this.props)}
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
    fields: state.fields
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