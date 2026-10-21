<?php

/*
 * The question bank. One row per question:
 *   [key, category, question, correct answer, [wrong answers], optional image path]
 *
 * Keys must stay stable: `php artisan db:seed` matches on them, so edits update in place.
 * Answer order on screen is shuffled once per key (see QuestionSeeder), numbers are sorted instead.
 *
 * Categories: food, movies, geography, science, sports, tech, emoji, education,
 *             monsters, current, retro, luma
 */

return [
    // Food & drink
    ['F1', 'food', 'Which country is the Mojito from?', 'Cuba', ['Mexico', 'Brazil', 'Spain']],
    ['F2', 'food', 'What is the main spirit in a Margarita?', 'Tequila', ['Rum', 'Vodka', 'Gin']],
    ['F3', 'food', 'Which Italian city is credited with inventing pizza?', 'Naples', ['Rome', 'Milan', 'Florence']],
    ['F4', 'food', 'Which cheese is traditionally in a Greek salad?', 'Feta', ['Brie', 'Cheddar', 'Gouda']],
    ['F5', 'food', 'After water, what is the most-consumed drink in the world?', 'Tea', ['Coffee', 'Beer', 'Soda']],
    ['F8', 'food', 'What is hummus mainly made from?', 'Chickpeas', ['Lentils', 'Black beans', 'Soybeans']],
    ['F12', 'food', 'What does a sommelier specialize in?', 'Wine', ['Beer', 'Cheese', 'Coffee']],
    ['F14', 'food', 'Which coffee drink\'s name means "stained" or "marked" in Italian?', 'Macchiato', ['Latte', 'Ristretto', 'Americano']],
    ['F15', 'food', 'Bubble tea originated in which country?', 'Taiwan', ['Japan', 'Thailand', 'China']],
    ['F17', 'food', 'Which energy drink says it "gives you wings"?', 'Red Bull', ['Monster', 'Rockstar', 'Gatorade']],
    ['F18', 'food', 'What is the most popular pizza topping in the US?', 'Pepperoni', ['Mushrooms', 'Sausage', 'Onions']],
    ['F19', 'food', 'Which country does pad thai come from?', 'Thailand', ['Vietnam', 'Laos', 'Cambodia']],

    // Movies, TV & music
    ['M2', 'movies', 'In the US version of The Office, what is the paper company called?', 'Dunder Mifflin', ['Vance Refrigeration', 'Prestige Worldwide', 'Initech']],
    ['M3', 'movies', 'Which show features Walter White and Jesse Pinkman?', 'Breaking Bad', ['Ozark', 'Better Call Saul', 'The Wire']],
    ['M4', 'movies', 'Who sang "Rolling in the Deep"?', 'Adele', ['Beyoncé', 'Rihanna', 'Lady Gaga']],
    ['M6', 'movies', 'Which band released the album "Rumours"?', 'Fleetwood Mac', ['Eagles', 'ABBA', 'The Doobie Brothers']],
    ['M8', 'movies', 'Which animated film features the song "Let It Go"?', 'Frozen', ['Tangled', 'Moana', 'Brave']],
    ['M10', 'movies', 'What is the name of Harry Potter\'s owl?', 'Hedwig', ['Errol', 'Pigwidgeon', 'Crookshanks']],
    ['M13', 'movies', 'Which artist released the album "Lemonade"?', 'Beyoncé', ['Rihanna', 'Adele', 'Taylor Swift']],
    ['M17', 'movies', 'Which musician is nicknamed "The Boss"?', 'Bruce Springsteen', ['Billy Joel', 'Tom Petty', 'Bob Seger']],
    ['M21', 'movies', 'Which sitcom features the character Sheldon Cooper?', 'The Big Bang Theory', ['Modern Family', 'New Girl', 'Parks and Recreation']],
    ['M28', 'movies', 'What is the name of the town in The Simpsons?', 'Springfield', ['Shelbyville', 'Quahog', 'Pawnee']],
    ['M29', 'movies', 'In Star Wars, who is Luke Skywalker\'s father?', 'Darth Vader', ['Obi-Wan Kenobi', 'Emperor Palpatine', 'Yoda']],

    // Geography & travel
    ['G1', 'geography', 'What is the capital of Canada?', 'Ottawa', ['Toronto', 'Montreal', 'Vancouver']],
    ['G4', 'geography', 'In which country is Mount Kilimanjaro?', 'Tanzania', ['Kenya', 'Uganda', 'Ethiopia']],
    ['G5', 'geography', 'What is the smallest country in the world by area?', 'Vatican City', ['Monaco', 'San Marino', 'Liechtenstein']],
    ['G7', 'geography', 'Which is the largest ocean?', 'Pacific', ['Atlantic', 'Indian', 'Arctic']],
    ['G10', 'geography', 'Which country has the largest population?', 'India', ['China', 'United States', 'Indonesia']],
    ['G11', 'geography', 'Which US state is the largest by area?', 'Alaska', ['Texas', 'California', 'Montana']],
    ['G14', 'geography', 'Which US state is nicknamed the "Sunshine State"?', 'Florida', ['California', 'Arizona', 'Hawaii']],
    ['G15', 'geography', 'Machu Picchu is in which country?', 'Peru', ['Chile', 'Bolivia', 'Colombia']],
    ['G16', 'geography', 'Which city is nicknamed "The City of Light"?', 'Paris', ['London', 'Rome', 'Vienna']],
    ['G19', 'geography', 'Which continent has the most countries?', 'Africa', ['Asia', 'Europe', 'South America']],
    ['G21', 'geography', 'The Great Barrier Reef is off the coast of which country?', 'Australia', ['Brazil', 'Mexico', 'Indonesia']],

    // Science & nature
    ['S2', 'science', 'Which planet has the most known moons?', 'Saturn', ['Jupiter', 'Uranus', 'Neptune']],
    ['S5', 'science', 'What is the largest animal on Earth?', 'Blue whale', ['African elephant', 'Fin whale', 'Sperm whale']],
    ['S7', 'science', 'What is the chemical symbol for gold?', 'Au', ['Ag', 'Gd', 'Go']],
    ['S8', 'science', 'What is the fastest land animal?', 'Cheetah', ['Lion', 'Pronghorn', 'Greyhound']],
    ['S9', 'science', 'Which is the only mammal capable of true flight?', 'Bat', ['Flying squirrel', 'Sugar glider', 'Colugo']],
    ['S10', 'science', 'Which gas makes up most of Earth\'s atmosphere?', 'Nitrogen', ['Oxygen', 'Carbon dioxide', 'Argon']],
    ['S11', 'science', 'About how long does sunlight take to reach Earth?', '8 minutes', ['8 seconds', '8 hours', '8 days']],
    ['S14', 'science', 'What do bees collect from flowers to make honey?', 'Nectar', ['Pollen', 'Sap', 'Dew']],
    ['S15', 'science', 'What is the largest organ of the human body?', 'Skin', ['Liver', 'Lungs', 'Heart']],
    ['S17', 'science', 'What is the most abundant element in the universe?', 'Hydrogen', ['Helium', 'Oxygen', 'Carbon']],
    ['S19', 'science', 'What type of animal is a Komodo dragon?', 'A lizard', ['A snake', 'A crocodile', 'A turtle']],
    ['S21', 'science', 'How many legs does a spider have?', 'Eight', ['Six', 'Ten', 'Twelve']],
    ['S22', 'science', 'How many colors are in a rainbow?', 'Seven', ['Five', 'Six', 'Eight']],

    // Sports
    ['P2', 'sports', 'How many rings are on the Olympic flag?', 'Five', ['Four', 'Six', 'Seven']],
    ['P4', 'sports', 'In golf, what is one stroke under par called?', 'Birdie', ['Eagle', 'Bogey', 'Albatross']],
    ['P5', 'sports', 'How many points is a touchdown worth, before the extra point?', '6', ['3', '5', '7']],
    ['P6', 'sports', 'Where is the Wimbledon tennis tournament held?', 'London', ['Paris', 'New York', 'Melbourne']],
    ['P10', 'sports', 'Which country has won the most men\'s FIFA World Cups?', 'Brazil', ['Germany', 'Italy', 'Argentina']],
    ['P11', 'sports', 'How many holes are in a full round of golf?', '18', ['9', '12', '24']],
    ['P12', 'sports', 'Which swimmer has won the most Olympic gold medals?', 'Michael Phelps', ['Mark Spitz', 'Ryan Lochte', 'Ian Thorpe']],
    ['P13', 'sports', 'What color jersey does the Tour de France leader wear?', 'Yellow', ['Green', 'Polka dot', 'White']],
    ['P14', 'sports', 'In tennis, what is a score of zero called?', 'Love', ['Nil', 'Zero', 'Duck']],
    ['P15', 'sports', 'How many players per side are on the court in volleyball?', 'Six', ['Five', 'Seven', 'Eight']],
    ['P16', 'sports', 'The Stanley Cup is awarded in which sport?', 'Ice hockey', ['Basketball', 'Baseball', 'Lacrosse']],
    ['P18', 'sports', 'How many minutes are in a regulation soccer match?', '90', ['60', '80', '100']],
    ['P19', 'sports', 'In bowling, what is it called when you roll three strikes in a row?', 'A turkey', ['A hat trick', 'A triple', 'An eagle']],

    // Tech & internet history
    ['T1', 'tech', 'Who is credited with inventing the World Wide Web?', 'Tim Berners-Lee', ['Vint Cerf', 'Bill Gates', 'Steve Jobs']],
    ['T3', 'tech', 'What programming language is Laravel written in?', 'PHP', ['Python', 'Ruby', 'JavaScript']],
    ['T8', 'tech', 'Which company was originally an online bookstore?', 'Amazon', ['eBay', 'Google', 'Netflix']],
    ['T9', 'tech', 'Which company started by mailing DVDs to customers?', 'Netflix', ['Hulu', 'Blockbuster', 'Amazon']],
    ['T11', 'tech', 'What year did the first iPhone launch?', '2007', ['2005', '2009', '2011']],
    ['T21', 'tech', 'What was the name of the paperclip helper in old Microsoft Office?', 'Clippy', ['Bob', 'Merlin', 'Rover']],
    ['T23', 'tech', 'What is the most-visited website in the world?', 'Google', ['YouTube', 'Facebook', 'Wikipedia']],
    ['T25', 'tech', 'Which pixelated block-building game did Microsoft buy in 2014?', 'Minecraft', ['Roblox', 'Fortnite', 'Terraria']],
    ['T26', 'tech', 'Which site calls itself "the front page of the internet"?', 'Reddit', ['Digg', 'Tumblr', 'Quora']],

    // Emoji decoder
    ['E1', 'emoji', 'Guess the movie: 🦁👑', 'The Lion King', ['Madagascar', 'The Jungle Book', 'Zootopia']],
    ['E2', 'emoji', 'Guess the movie: 🚢🧊❤️', 'Titanic', ['Poseidon', 'The Little Mermaid', 'Moana']],
    ['E5', 'emoji', 'Guess the movie: 👻🚫', 'Ghostbusters', ['Beetlejuice', 'Casper', 'Poltergeist']],
    ['E8', 'emoji', 'Guess the movie: 🧞🪔🐒', 'Aladdin', ['Mulan', 'Moana', 'Pocahontas']],
    ['E12', 'emoji', 'Guess the movie: 🤠🧸🚀', 'Toy Story', ['Coco', 'Cars', 'Monsters, Inc.']],
    ['E16', 'emoji', 'Guess the movie: 🐻🍯🎈', 'Winnie the Pooh', ['Paddington', 'Yogi Bear', 'Brave']],
    ['E18', 'emoji', 'Guess the movie: 🦖🧬🌴', 'Jurassic Park', ['Godzilla', 'King Kong', 'Land of the Lost']],
    ['E27', 'emoji', 'Guess the movie: 🌪️👠🦁', 'The Wizard of Oz', ['Alice in Wonderland', 'Mary Poppins', 'Peter Pan']],
    ['E31', 'emoji', 'Guess the movie: 🟢🐴👸', 'Shrek', ['Frozen', 'The Hulk', 'Monsters, Inc.']],

    // Education & learning
    ['L2', 'education', 'What does STEM stand for?', 'Science, Technology, Engineering, Math', ['Science, Teaching, English, Music', 'Study, Test, Evaluate, Master', 'Skills, Tools, Education, Methods']],
    ['L5', 'education', 'Which letter is 13th in the alphabet?', 'M', ['L', 'N', 'O']],
    ['L8', 'education', 'What is the square root of 144?', '12', ['11', '14', '16']],
    ['L11', 'education', 'In what year was the US Declaration of Independence adopted?', '1776', ['1789', '1492', '1812']],
    ['L14', 'education', 'How many minutes are in a day?', '1,440', ['1,200', '1,640', '2,400']],
    ['L16', 'education', 'Which science studies rocks and the structure of the Earth?', 'Geology', ['Meteorology', 'Astronomy', 'Ecology']],
    ['L17', 'education', 'Which Roman numeral stands for 50?', 'L', ['C', 'D', 'X']],
    ['L20', 'education', 'What do you call a word that means the opposite of another word?', 'Antonym', ['Synonym', 'Homonym', 'Acronym']],
    ['L22', 'education', 'In which year did humans first land on the Moon?', '1969', ['1965', '1972', '1975']],

    // Luma monsters
    ['N1', 'monsters', 'Which Luma monster is this?', 'Fred', ['Brush', 'Nugget', 'Squiggles'], '/images/questions/fred.svg'],
    ['N3', 'monsters', 'Which Luma monster is this?', 'Orbee', ['Widget', 'Pebbles', 'Squiggles'], '/images/questions/orbee.svg'],
    ['N4', 'monsters', 'Which Luma monster is this?', 'Dizzil Chester', ['Nugget', 'Brush', 'Pebbles'], '/images/questions/dizzil.svg'],
    ['N5', 'monsters', 'Which Luma monster is this?', 'Buttercup', ['Widget', 'Pebbles', 'Tom'], '/images/questions/buttercup.svg'],
    ['N7', 'monsters', 'Which monster is Nugget\'s best friend?', 'Brush', ['Fred', 'Tom', 'Widget']],
    ['N8', 'monsters', 'Which monster is Nugget\'s loyal pet?', 'Dizzil Chester', ['Orbee', 'Widget', 'Squiggles']],

    // Current pop culture
    ['X1', 'current', 'Which Netflix show features deadly children\'s games like "Red Light, Green Light"?', 'Squid Game', ['Alice in Borderland', 'Black Mirror', 'The Platform']],
    ['X2', 'current', 'Which pop star headlined the record-breaking "Eras Tour"?', 'Taylor Swift', ['Beyoncé', 'Adele', 'Olivia Rodrigo']],
    ['X6', 'current', 'Which app made short dance videos a worldwide trend?', 'TikTok', ['Instagram', 'Snapchat', 'Vine']],
    ['X8', 'current', 'Which 2022 sequel brought Tom Cruise back as Maverick?', 'Top Gun: Maverick', ['Days of Thunder', 'Edge of Tomorrow', 'Mission: Impossible – Fallout']],
    ['X14', 'current', 'Which new emotion takes center stage in Inside Out 2?', 'Anxiety', ['Envy', 'Nostalgia', 'Boredom']],
    ['X17', 'current', 'Which AI chatbot from OpenAI went viral in late 2022?', 'ChatGPT', ['Siri', 'Alexa', 'Cortana']],

    // 80s & 90s
    ['R1', 'retro', 'Which band sang "Don\'t Stop Believin\'"?', 'Journey', ['Foreigner', 'Boston', 'Toto']],
    ['R2', 'retro', 'Which Michael Jackson album, released in 1982, is the best-selling of all time?', 'Thriller', ['Bad', 'Off the Wall', 'Dangerous']],
    ['R3', 'retro', 'In which movie does Marty McFly travel through time in a DeLorean?', 'Back to the Future', ['Bill & Ted\'s Excellent Adventure', 'The Terminator', 'Time Bandits']],
    ['R4', 'retro', 'Which boy band sang "I Want It That Way"?', 'Backstreet Boys', ['*NSYNC', 'New Kids on the Block', '98 Degrees']],
    ['R6', 'retro', 'In which movie does a boy befriend an alien who wants to "phone home"?', 'E.T. the Extra-Terrestrial', ['Close Encounters of the Third Kind', 'Alien', 'Cocoon']],
    ['R7', 'retro', 'What was the hairstyle "business in the front, party in the back" called?', 'A mullet', ['A mohawk', 'A bob', 'A pompadour']],
    ['R8', 'retro', 'Which handheld game system did Nintendo release in 1989?', 'Game Boy', ['Game Gear', 'Atari Lynx', 'PSP']],
    ['R10', 'retro', 'Which sitcom is set in the NYC coffee shop Central Perk?', 'Friends', ['Seinfeld', 'Frasier', 'Cheers']],
    ['R11', 'retro', 'Who is known as the "King of Pop"?', 'Michael Jackson', ['Prince', 'Elvis Presley', 'Madonna']],

    // Luma (every game includes all of these)
    ['LU1', 'luma', 'Which domain do we use for our email addresses?', 'learnwithluma.com', ['enuggetlearning.com', 'lumabrighterlearning.com', 'learnwithluma-ish.com']],
    ['LU2', 'luma', 'Which domain is our customer application on?', 'enuggetlearning.com', ['learnwithluma.com', 'lumabrighterlearning.com', 'learnwithluma-ish.com']],
    ['LU3', 'luma', 'Who is Luma\'s longest-tenured active employee, other than the founders?', 'Elizabeth', ['Dean', 'Ryan', 'Julie']],
];
