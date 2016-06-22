/**
 * Question Service
 * @param {ChoiceQuestionService}  ChoiceQuestionService
 * @param {ClozeQuestionService}   ClozeQuestionService
 * @param {GraphicQuestionService} GraphicQuestionService
 * @param {MatchQuestionService}   MatchQuestionService
 * @param {OpenQuestionService}    OpenQuestionService
 * @constructor
 */
function QuestionService(
    ChoiceQuestionService,
    ClozeQuestionService,
    GraphicQuestionService,
    MatchQuestionService,
    OpenQuestionService
) {
    this.services = {};

    // Inject custom services
    this.services['application/x.choice+json']  = ChoiceQuestionService;
    this.services['application/x.match+json']   = MatchQuestionService;
    this.services['application/x.cloze+json']   = ClozeQuestionService;
    this.services['application/x.short+json']   = OpenQuestionService;
    this.services['application/x.graphic+json'] = GraphicQuestionService;
};

/**
 * Get the Service that manage the QuestionType
 * @return {AbstractQuestionService}
 */
QuestionService.prototype.getTypeService = function getTypeService(questionType) {
    var service = null;
    if (!this.services[questionType]) {
        console.error('Question Type : try to get a Service for an undefined type `' + questionType + '`.');
    } else {
        service = this.services[questionType];
    }

    return service;
};

/**
 * Get a Question from its ID
 * @param {Array} questions
 * @param {String} id
 * @returns {Object}
 */
QuestionService.prototype.getQuestion = function getQuestion(questions, id) {
    var question = null;
    for (var i = 0; i < questions.length; i++) {
        if (id === questions[i].id) {
            question = questions[i];
            break; // Stop searching
        }
    }

    return question;
};

export default QuestionService
