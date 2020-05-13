const API_REQUEST = 'API_REQUEST'

export const actions = {}

actions.downloadWikiPdf = (wikiId) => ({
  [API_REQUEST]: {
    url: ['apiv2_wiki_export_pdf', {id: wikiId}],
    request: {
      method: 'GET'
    }
  }
})
