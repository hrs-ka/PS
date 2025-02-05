# Imports
import chess
import chess.polyglot
import time
import random
import chess.syzygy
import chess.pgn
import pstables

PAWNS_PIECE_TABLE = pstables.pawns_table
PAWNS_PIECE_TABLE.reverse()

KNIGHTS_PIECE_TABLE = pstables.knights_table
KNIGHTS_PIECE_TABLE.reverse()

BISHOPS_PIECE_TABLE = pstables.bishops_table
BISHOPS_PIECE_TABLE.reverse()

ROOKS_PIECE_TABLE = pstables.rooks_table
ROOKS_PIECE_TABLE.reverse()

QUEENS_PIECE_TABLE = pstables.queens_table
QUEENS_PIECE_TABLE.reverse()

KING_MIDDLE_PIECE_TABLE = pstables.king_middle_table
KING_MIDDLE_PIECE_TABLE.reverse()

KING_END_PIECE_TABLE = pstables.king_end_table
KING_END_PIECE_TABLE.reverse()

WIN_VALUE = 1E6

LINE = "-" * 40 + "\n"

COLUMNS = ["a", "b", "c", "d", "e", "f", "g", "h"]


# Returns game mode choice number.
def start_game():
    print(
        "  1. Player    vs.    Player\n  2. Player    vs.    Computer \n  3. Computer  vs.    Computer"
    )

    # In main
    return int(input("Choice: "))


# find and return the book move if exists
# if no book move, return None
def find_opening(book, board):
    with chess.polyglot.open_reader(book) as reader:
        for entry in reader.find_all(board):
            opening = entry.move
            return opening


def find_pins(board):

    piece_dif = 0

    for square in board.pieces(chess.KNIGHT, chess.WHITE):
        if board.is_pinned(chess.WHITE, square):
            piece_dif -= 100
    for square in board.pieces(chess.KNIGHT, chess.BLACK):
        if board.is_pinned(chess.BLACK, square):
            piece_dif += 100
    for square in board.pieces(chess.ROOK, chess.WHITE):
        if board.is_pinned(chess.WHITE, square):
            piece_dif -= 200
    for square in board.pieces(chess.ROOK, chess.BLACK):
        if board.is_pinned(chess.BLACK, square):
            piece_dif += 200
    for square in board.pieces(chess.QUEEN, chess.WHITE):
        if board.is_pinned(chess.WHITE, square):
            piece_dif -= 500
    for square in board.pieces(chess.QUEEN, chess.BLACK):
        if board.is_pinned(chess.BLACK, square):
            piece_dif += 500

    return piece_dif


# find the endgame value from the tablebases
def endgame_value(board):
    with chess.syzygy.open_tablebase("Endgame Tables") as tablebase:
        ev = tablebase.probe_wdl(board)
        print(ev)
    return 0


def piece_square_evaluation(board):
    w_value = 0
    b_value = 0

    # Piece types to check tables of
    piece_types = [
        chess.PAWN, chess.KNIGHT, chess.BISHOP, chess.ROOK, chess.QUEEN,
        chess.KING
    ]

    # Tables of piece types to be checked
    piece_tables = [
        PAWNS_PIECE_TABLE, KNIGHTS_PIECE_TABLE, BISHOPS_PIECE_TABLE,
        ROOKS_PIECE_TABLE, QUEENS_PIECE_TABLE, KING_MIDDLE_PIECE_TABLE
    ]

    # Loops for the piece types
    for p in range(6):

        # Assigns the current position tables of the board of a given piece
        w_board = board.pieces(piece_types[p], chess.WHITE)
        b_board = board.pieces(piece_types[p], chess.BLACK)

        # Loops for the white and black current piece square tables adding the values of the pieces on each square to w_value and b_value
        for w_pieces in range(len(w_board)):
            w_value += (piece_tables[p])[w_board.pop()]
        for b_pieces in range(len(b_board)):
            b_value += (piece_tables[p])[b_board.pop()]

    return w_value, b_value


def evaluation(board, outcome):

    # game over?
    if outcome is not None:
        #checkmate
        if outcome.termination == 1:
            return WIN_VALUE
        elif outcome.termination == -1:
            return -WIN_VALUE

    num_pieces_on_board = 0

    white_piece_value = 0
    black_piece_value = 0

    if board.turn:
        white_piece_value += len(list(board.legal_moves)) * 5

    else:
        black_piece_value += len(list(board.legal_moves)) * 5

    # pawns
    white_pawn_count = len(board.pieces(chess.PAWN, chess.WHITE)) * 100
    white_piece_value += white_pawn_count
    num_pieces_on_board += white_pawn_count

    black_pawn_count = len(board.pieces(chess.PAWN, chess.BLACK)) * 100
    black_piece_value += black_pawn_count
    num_pieces_on_board += black_pawn_count

    # knights
    white_knight_count = len(board.pieces(chess.KNIGHT, chess.WHITE))
    white_piece_value += white_knight_count * 305
    num_pieces_on_board += white_knight_count

    black_knight_count = len(board.pieces(chess.KNIGHT, chess.BLACK))
    black_piece_value += black_knight_count * 305
    num_pieces_on_board += black_knight_count

    # bishops
    white_bishop_count = len(board.pieces(chess.BISHOP, chess.WHITE))
    white_piece_value += white_bishop_count * 333
    num_pieces_on_board += white_bishop_count

    black_bishop_count = len(board.pieces(chess.BISHOP, chess.BLACK))
    black_piece_value += black_bishop_count * 333
    num_pieces_on_board += black_bishop_count

    # rooks
    white_rook_count = len(board.pieces(chess.ROOK, chess.WHITE))
    white_piece_value += white_rook_count * 563
    num_pieces_on_board += white_rook_count

    black_rook_count = len(board.pieces(chess.ROOK, chess.BLACK))
    black_piece_value += black_rook_count * 563
    num_pieces_on_board += black_rook_count

    # queens
    white_queen_count = len(board.pieces(chess.QUEEN, chess.WHITE))
    white_piece_value += white_queen_count * 950
    num_pieces_on_board += white_queen_count

    black_queen_count = len(board.pieces(chess.QUEEN, chess.BLACK))
    black_piece_value += black_queen_count * 950
    num_pieces_on_board += black_queen_count

    # check end game tables for win/loss
    if num_pieces_on_board < 7:
        endgame_result = endgame_value(board)
        if endgame_result > 0:
            return WIN_VALUE - 1
        if endgame_result < 0:
            return -WIN_VALUE + 1

    # include values for pins and checks
    white_piece_value += find_pins(board)

    #Piece square
    white_ps_value, black_ps_value = piece_square_evaluation(board)
    white_piece_value += white_ps_value
    black_piece_value += black_ps_value

    return white_piece_value - black_piece_value


def minimax(board, player, depth, alpha, beta):
    outcome = board.outcome()
    if depth == 0 or outcome is not None:
        return evaluation(board, outcome), None
    bestMove = None
    if player:
        bestValue = -WIN_VALUE
        for move in board.legal_moves:
            board.push(move)
            value, _ = minimax(board, False, depth - 1, alpha, beta)
            board.pop()
            if value > bestValue:
                bestMove = move
                bestValue = value
            if bestValue >= beta:
                break
            alpha = max(alpha, bestValue)
        return bestValue, bestMove
    else:
        bestValue = WIN_VALUE
        for move in board.legal_moves:
            board.push(move)
            value, _ = minimax(board, True, depth - 1, alpha, beta)
            board.pop()
            if value < bestValue:
                bestMove = move
                bestValue = value
            if bestValue <= alpha:
                break
            beta = min(beta, bestValue)
        return bestValue, bestMove


def iterative_deepening(board, time_limit):
    depth = 1
    start_time = time.time()
    time_taken = 0
    move = None
    max_allowed_depth = 20  # Prevent excessive recursion depth
    while depth <= max_allowed_depth and time_taken < 0.5 * time_limit:
        alpha = -WIN_VALUE
        beta = WIN_VALUE
        value, current_move = minimax(board, board.turn, depth, alpha, beta)
        if current_move is not None:
            move = current_move  # Update move only if a valid one is found
        print(f"After depth {depth} search, best move is {move}")
        time_taken = time.time() - start_time
        depth += 1
    print(f"Playing best move: {move}")
    return move

def get_move(board, time_limit):
    # Adds an opening from each book into a list and shuffles.
    opening_moves = [
        find_opening("baron30.bin", board),
        find_opening("human.bin", board),
        find_opening("titans.bin", board)
    ]
    random.shuffle(opening_moves)

    # If there is an opening return the first opening.
    for opening in opening_moves:
        if opening is not None:
            print("Using a book move", opening)
            return opening

    return iterative_deepening(board, time_limit)


def pgn_import(board):
    pgn_correct = False
    while not pgn_correct:
        try:
            filename = input("Enter the filename: ")
            if filename.split(".")[-1] == "pgn":
                pgn = open(filename)
            else:
                pgn = open(filename + ".pgn")

            first_game = chess.pgn.read_game(pgn)

            pgn_correct = True

            # Iterate through all moves and play them on a board.
            #board = first_game.board()
            for move in first_game.mainline_moves():
                board.push(move)
        except:
            print("Game not found.\n")


# Make board
board = chess.Board()

# Retrieve game mode number.
game = start_game()

# Player v Player
if game == 1:
    turn = 1
    while not board.is_checkmate():
        print()
        print(board)
        print()
        print(board.legal_moves)
        if turn % 2 == 0:
           blackmove = input("\nBlack's move: ")
           board.push_san(blackmove)
        else:
            whitemove = input("\nWhite's move: ")
            board.push_san(whitemove)
        turn += 1

# Player v Computer
elif game == 2:
    print()

    # Load pgn or not
    loadgame = input("Load a game? Y/n: ")
    if loadgame.lower() == "y":
        loadgame = True
        pgn_import(board)
    else:
        loadgame = False

    # If the colour is not determined by pgn ask preference
    if not loadgame:
        if game == 2:
            player_colour = input("\nBlack or White?\n")
            turn = 1
            if player_colour.lower() == "black":
                turn = 2
    else:
        if board.turn:
            turn = 1
        else:
            turn = 2

    time_limit = int(input("Enter time limit for iterative deepening: \n"))

    while not board.is_checkmate():
        if turn % 2 == 0:
            print(LINE)
            print("Computer's move")
            board.push(get_move(board, time_limit))
            time.sleep(1)
        else:
            move_legal = False
            while not move_legal:
                print(LINE)
                print(board)
                print()
                print(board.legal_moves)
                try:
                    player_move = input("\nPlayer's move: ")
                    if player_move.lower() == "undo":
                        print("\nPopping 2 moves\n")
                        board.pop()
                        board.pop()
                        time.sleep(1)
                    else:
                        board.push_san(player_move)
                        move_legal = True
                    # change loop condition

                except:
                    print("Illegal move")
        print()
        turn += 1

# Local Computer v Computer
elif game == 3:
    move_number = 0
    time_limit = int(input("Enter time limit for iterative deepening: "))
    while not board.is_checkmate():
        colour_turn = "White" if board.turn else "Black"
        # white or black?
        board.push(get_move(board, time_limit, p))
        move_number += 1
        print(LINE)
        print("Move number " + str(move_number) + ", Colour", colour_turn)
        print()
        print(board)
        print()

# Lichess Computer v Computer??
elif game == 4:
    while not board.is_checkmate():
        # Find if white or black to determine when turn is.
        board.push(get_move(board))

