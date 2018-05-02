import {PropTypes as T} from 'prop-types'

import {User as UserType} from '#/main/core/user/prop-types'

const Category = {
  propTypes: {
    id: T.string,
    name: T.string,
    details: T.shape({
      notify_addition: T.bool,
      notify_edition: T.bool,
      notify_removal: T.bool,
      notify_pending_comment: T.bool,
      color: T.string
    }),
    managers: T.arrayOf(T.shape({
      id: T.string.isRequired,
      firstName: T.string.isRequired,
      lastName: T.string.isRequired,
      username: T.string.isRequired
    }))
  }
}

const Keyword = {
  propTypes: {
    id: T.string,
    name: T.string
  }
}

const Field = {
  propTypes: {
    id: T.string,
    autoId: T.number,
    name: T.string,
    type: T.string,
    required: T.bool,
    restrictions: T.shape({
      isMetadata: T.bool,
      locked: T.bool,
      lockedEditionOnly: T.bool,
      hidden: T.bool,
      order: T.number
    }),
    help: T.string,
    options: T.oneOfType([
      T.shape({
        file_types: T.arrayOf(T.string),
        nb_files_max: T.number
      }),
      T.array
    ]),
    fieldFacet: T.shape({
      id: T.string.isRequired,
      name: T.string.isRequired,
      type: T.string.isRequired,
      required: T.bool.isRequired,
      restrictions: T.shape({
        hidden: T.bool.isRequired,
        isMetadata: T.bool.isRequired,
        locked: T.bool.isRequired,
        lockedEditionOnly: T.bool.isRequired,
        order: T.number
      }).isRequired
    })
  }
}

const ClacoForm = {
  propTypes: {
    id: T.string.isRequired,
    autoId: T.number.isRequired,
    details: T.shape({
      max_entries: T.number,
      creation_enabled: T.bool,
      edition_enabled: T.bool,
      moderated: T.bool,
      default_home: T.string,
      display_nb_entries: T.string,
      menu_position: T.string,
      random_enabled: T.bool,
      random_categories: T.array,
      random_start_date: T.string,
      random_end_date: T.string,
      search_enabled: T.bool,
      search_column_enabled: T.bool,
      search_columns: T.array,
      display_metadata: T.string,
      locked_fields_for: T.string,
      display_categories: T.bool,
      open_categories: T.bool,
      comments_enabled: T.bool,
      anonymous_comments_enabled: T.bool,
      moderate_comments: T.string,
      display_comments: T.bool,
      open_comments: T.bool,
      display_comment_author: T.bool,
      display_comment_date: T.bool,
      comments_roles: T.array,
      comments_display_roles: T.array,
      votes_enabled: T.bool,
      display_votes: T.bool,
      open_votes: T.bool,
      votes_start_date: T.string,
      votes_end_date: T.string,
      keywords_enabled: T.bool,
      new_keywords_enabled: T.bool,
      display_keywords: T.bool,
      open_keywords: T.bool,
      use_template: T.bool,
      default_display_mode: T.string,
      display_title: T.string,
      display_subtitle: T.string,
      display_content: T.string
    }).isRequired,
    template: T.string,
    categories: T.arrayOf(T.shape(Category.propTypes)),
    keywords: T.arrayOf(T.shape(Keyword.propTypes)),
    fields: T.arrayOf(T.shape(Field.propTypes))
  }
}

const Entry = {
  propTypes: {
    id: T.string,
    autoId: T.number,
    title: T.string,
    status: T.number,
    locked: T.bool,
    creationDate: T.string,
    editionDate: T.string,
    publicationDate: T.string,
    user: T.object,
    clacoForm: T.object,
    values: T.object
  }
}

const EntryUser = {
  propTypes: {
    id: T.string,
    autoId: T.number,
    shared: T.bool,
    notifyEdition: T.bool,
    notifyComment: T.bool,
    notifyVote: T.bool,
    user: T.object,
    entry: T.object
  }
}

const Comment = {
  propTypes: {
    id: T.string,
    content: T.string,
    status: T.number,
    creationDate: T.string,
    editionDate: T.string,
    user: T.shape(UserType.propTypes)
  }
}

export {
  Category,
  Keyword,
  Field,
  ClacoForm,
  Entry,
  EntryUser,
  Comment
}