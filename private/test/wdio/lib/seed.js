var syncRequest = require('sync-request');

function Seed(){}

Seed.prototype.growEmpty = function(baseURL) {
    this.grow(baseURL, {
        Schema : {
            Drop: [],
            Update: [true]
        }
    });
};

Seed.prototype.growBasic = function(baseURL) {
    this.grow(baseURL, {
        Schema : {
            Drop: [],
            Update: [true]
        },
        University: {
            BaDresden: []
        },
        ScheduledEvent : {
            ExpireTokenPurge: [],
            DiningHallMenuFetch : ['Moose\Extension\DiningHall\MensaJohannstadtLoader'],
            MailSend: []
        },
        'FieldOfStudy:1' : {
            Informationstechnologie: [],
            Medieninformatik: []
        },
        TutorialGroup : {
            Deterministic: []
        },
        Course : {
            Deterministic : [25]
        },
        'FieldOfStudy:2' : {
            AddDeterministicCourses : [1]
        },
        User : {
            Admin: [],
            Deterministic : [20, 'password']
        },
        Thread : {
            Deterministic : [50]
        },
        Post : {
            Deterministic : [100]
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