#-- vim:sw=2:et
#++
#
# :title: Markov plugin
#
# Author:: Tom Gilbert <tom@linuxbrit.co.uk>
# Copyright:: (C) 2005 Tom Gilbert
#
# Contribute to chat with random phrases built from word sequences learned
# by listening to chat
#
# H4X0R3D into a something by Kai Tamkun

def debug(lolz)
	puts '> '+lolz
end

class MarkovPlugin
  def initialize
    super
    @learning_queue = Queue.new
    @learning_thread = Thread.new do
      while s = @learning_queue.pop
        learn s
        sleep 0.5
      end
    end
    @learning_thread.priority = -1
  end

  def cleanup
    debug 'closing learning thread'
    @learning_queue.push nil
    @learning_thread.join
    debug 'learning thread closed'
  end

  def generate_string(word1, word2)
    # limit to max of markov.max_words words
    output = word1 + " " + word2

    # try to avoid :nonword in the first iteration
    wordlist = @registry["#{word1} #{word2}"]
    wordlist.delete(:nonword)
    if not wordlist.empty?
      word3 = wordlist[rand(wordlist.length)]
      output = output + " " + word3
      word1, word2 = word2, word3
    end

    (15).times do
      wordlist = @registry["#{word1} #{word2}"]
      break if wordlist.empty?
      word3 = wordlist[rand(wordlist.length)]
      break if word3 == :nonword
      output = output + " " + word3
      word1, word2 = word2, word3
    end
    return output
  end

  def clean_str(s)
    str = s.dup
    str.gsub!(/^\S+[:,;]/, "")
    str.gsub!(/\s{2,}/, ' ') # fix for two or more spaces
    return str.strip
  end
  
  def should_talk
    return true
  end

  def delay
    0
  end

  def random_markov(m, message)
    return unless should_talk

    word1, word2 = message.split(/\s+/)
    return unless word1 and word2
    line = generate_string(word1, word2)
    return unless line
    # we do nothing if the line we return is just an initial substring
    # of the line we received
    return if message.index(line) == 0
    puts line
  end

  def chat(m, params)
    line = generate_string(params[:seed1], params[:seed2])
    if line != "#{params[:seed1]} #{params[:seed2]}"
      puts line 
    else
      puts "I can't :("
    end
  end

  def rand_chat(m, params)
    # pick a random pair from the db and go from there
    word1, word2 = :nonword, :nonword
    output = Array.new
    50.times do
      wordlist = @registry["#{word1} #{word2}"]
      break if wordlist.empty?
      word3 = wordlist[rand(wordlist.length)]
      break if word3 == :nonword
      output << word3
      word1, word2 = word2, word3
    end
    if output.length > 1
      m.reply output.join(" ")
    else
      m.reply "I can't :("
    end
  end
  
  def message(m)
    return if ignore? m

    # in channel message, the kind we are interested in
    message = clean_str m.plainmessage

    if m.action?
      message = "#{m.sourcenick} #{message}"
    end
    
    @learning_queue.push message
    random_markov(m, message) unless m.replied?
  end

  def learn(message)
    # debug "learning #{message}"
    wordlist = message.split(/\s+/)
    return unless wordlist.length >= 2
    word1, word2 = :nonword, :nonword
    wordlist.each do |word3|
      k = "#{word1} #{word2}"
      @registry[k] = @registry[k].push(word3)
      word1, word2 = word2, word3
    end
    k = "#{word1} #{word2}"
    @registry[k] = @registry[k].push(:nonword)
  end
end

plugin = MarkovPlugin.new
plugin