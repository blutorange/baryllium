var syncRequest = require('sync-request');

function Seed(){}

Seed.prototype.growBasic = function(baseURL) {
    this.grow(baseURL, {
        'Schema' : {
            'Update': []
        },
        'ScheduledEvent' : {
            'ExpireTokenPurge': [],
            'DiningHallMenuFetch' : ['Moose\Extension\DiningHall\MensaJohannstadtLoader'],
            'MailSend': []
        },
        'FieldOfStudy:1' : {
            'Informationstechnologie': [],
            'Medieninformatik': []
        },
        'TutorialGroup' : {
            'Random': []
        },
        'Course' : {
            'Random' : [25]
        },
        'FieldOfStudy:2' : {
            'AddRandomCourses' : [1]
        },
        'User' : {
            'Admin': [],
            'Random' : [20, 'password']
        },
        'Thread' : {
            'Random' : [50]
        },
        'Post' : {
            'Random' : [100]
        }
    });
};

Seed.prototype.grow = function(baseURL, data) {
    var res = syncRequest('POST', baseURL + '/public/servlet/seed.php', {
        timeout: 60000,
        cache: false,
        json: data
    });
    var responseJSON = JSON.parse(res.getBody('utf8'));
    if (res.statusCode !== 200)
        throw new Exception('Server returned ' + res.statusCode);
    if (responseJSON.error) 
        throw new Exception('Seed request unsuccessful: ' + responseJSON.error.message + '(' + responseJSON.error.details + ')');                
};

module.exports = new Seed();
