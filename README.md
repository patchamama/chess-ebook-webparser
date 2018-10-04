# chess-ebook-webparser
Parser of chess websites or ePub chess ebook (without DRM) to view the diagrams in one browser, regenerate diagrams and study/reproduce the games.

# Getting Started
The easiest way to use it is to download a release, unpack that release, visit the URL (http://yourhost/parseChess.php) and upload a chess ePub file or a URL with chess post/games to parse.

## Example Screenshots
The following is a example screenshot from:

URL: _parseChess.php?url=http://blogs.deia.eus/ajedreztxiki/category/celadas-tipicas_

![GitHub Logo](/examples/example1.jpg)

# Features
This implementation has the following features:

- Parse websites/ePub (without DRM) to recognize pattern of chess notations and reproduce it...
- Allow to recognize chess notations in text or website or ePub file and to reproduce it in a interactive chessboard inserted in the text (allows also to add moves to a game or variants)
- Regenerate new diagrams images to be replaced

# Running the tests
Soon...

# Built With
PHP + Javascript

# Authors
Armando Urquiola - https://github.com/patchamama

# License
chess-ebook-webparser is licensed under the GPL 2.0, see the LICENSE.md file for details.

# Acknowledgments
We use the following libraries in the implementation:

- PgnViewerJS to view and reproduce the PGN Notation extracted (parsed): https://github.com/mliebelt/PgnViewerJS
- LT-PGN-VIEWER Javascript Library to conversions from PGN to FEN: http://www.lutanho.net/pgn/pgnviewer.html
- Chess Diagram Setup to generate new Diagrams images: http://www.jinchess.com/chessboard/composer/

Thank you a lot to all contributors of issues.

# to Do
- Integrate Stockfisch evaluation module https://github.com/exoticorn/stockfish-js
- Recognize more epub Structures (not all are supported)
- Define end language of result chess notation 
- Change font type, font size, margins... 
- Read and recognize text with Descriptive notation: https://en.wikipedia.org/wiki/Descriptive_notation & http://www.abdelnauer.de/js/notation.htm

