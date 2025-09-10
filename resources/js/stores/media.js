import { defineStore } from 'pinia';
import { useStorage } from '@vueuse/core';

export const useMediaStore = defineStore('media', {
  state: () => ({
    sermons: useStorage('church-sermons', [
      {
        id: 'sermon-1',
        title: 'Finding Peace in Troubled Times',
        speaker: 'Pastor Johnson',
        date: '2025-05-19T10:00:00',
        description: 'A message about finding God\'s peace even in the midst of life\'s challenges.',
        scripture: 'John 14:27',
        audioUrl: '/media/sermons/finding-peace.mp3',
        videoUrl: 'https://www.youtube.com/watch?v=example1',
        thumbnailUrl: '/media/thumbnails/finding-peace.jpg',
        tags: ['peace', 'faith', 'challenges'],
        series: 'Peace That Passes Understanding',
        duration: 35, // minutes
        downloads: 24,
        views: 142,
        notes: '/media/notes/finding-peace.pdf',
        transcript: false,
        isPublished: true
      },
      {
        id: 'sermon-2',
        title: 'The Power of Prayer',
        speaker: 'Pastor Johnson',
        date: '2025-05-12T10:00:00',
        description: 'Exploring the transformative power of prayer in our daily lives.',
        scripture: 'James 5:16',
        audioUrl: '/media/sermons/power-of-prayer.mp3',
        videoUrl: 'https://www.youtube.com/watch?v=example2',
        thumbnailUrl: '/media/thumbnails/power-of-prayer.jpg',
        tags: ['prayer', 'spiritual growth', 'faith'],
        series: 'Spiritual Disciplines',
        duration: 42, // minutes
        downloads: 31,
        views: 187,
        notes: '/media/notes/power-of-prayer.pdf',
        transcript: true,
        isPublished: true
      },
      {
        id: 'sermon-3',
        title: 'Living with Purpose',
        speaker: 'Guest Speaker - Dr. Smith',
        date: '2025-05-05T10:00:00',
        description: 'Discovering God\'s purpose for your life and living intentionally.',
        scripture: 'Jeremiah 29:11',
        audioUrl: '/media/sermons/living-with-purpose.mp3',
        videoUrl: 'https://www.youtube.com/watch?v=example3',
        thumbnailUrl: '/media/thumbnails/living-with-purpose.jpg',
        tags: ['purpose', 'calling', 'vision'],
        series: 'Purposeful Living',
        duration: 38, // minutes
        downloads: 19,
        views: 126,
        notes: '/media/notes/living-with-purpose.pdf',
        transcript: false,
        isPublished: true
      }
    ]),
    
    podcasts: useStorage('church-podcasts', [
      {
        id: 'podcast-1',
        title: 'Faith Conversations',
        host: 'Pastor Johnson & Youth Pastor Smith',
        description: 'Weekly discussions about faith and life for today\'s Christians.',
        coverArt: '/media/podcasts/faith-conversations.jpg',
        rssUrl: '/podcasts/faith-conversations.xml',
        episodes: [
          {
            id: 'episode-1',
            title: 'Faith in the Workplace',
            date: '2025-05-20T08:00:00',
            description: 'How to live out your faith in professional settings.',
            audioUrl: '/media/podcasts/faith-conversations/episode1.mp3',
            duration: 28, // minutes
            downloads: 45
          },
          {
            id: 'episode-2',
            title: 'Raising Faithful Children',
            date: '2025-05-13T08:00:00',
            description: 'Strategies for nurturing faith in the next generation.',
            audioUrl: '/media/podcasts/faith-conversations/episode2.mp3',
            duration: 32, // minutes
            downloads: 67
          }
        ],
        subscribers: 124,
        isPublished: true
      }
    ]),
    
    devotionals: useStorage('church-devotionals', [
      {
        id: 'devotional-1',
        title: 'Daily Bread',
        author: 'Pastor Johnson',
        description: 'Daily devotionals for spiritual growth and encouragement.',
        coverImage: '/media/devotionals/daily-bread.jpg',
        entries: [
          {
            id: 'entry-1',
            title: 'God\'s Faithfulness',
            date: '2025-05-25',
            scripture: 'Lamentations 3:22-23',
            content: 'The steadfast love of the LORD never ceases; his mercies never come to an end; they are new every morning; great is your faithfulness.',
            reflection: 'God\'s faithfulness is not dependent on our circumstances or feelings. It is a constant reality we can depend on every day.',
            prayer: 'Lord, thank you for your faithfulness that never fails. Help me to trust in your constant love and mercy today.'
          },
          {
            id: 'entry-2',
            title: 'Finding Strength',
            date: '2025-05-24',
            scripture: 'Isaiah 40:31',
            content: 'But they who wait for the LORD shall renew their strength; they shall mount up with wings like eagles; they shall run and not be weary; they shall walk and not faint.',
            reflection: 'Waiting on the Lord is not passive; it\'s actively trusting in His timing and power.',
            prayer: 'Father, give me the patience to wait on your timing and the faith to believe in your strength when I am weak.'
          }
        ],
        subscribers: 87,
        isPublished: true
      }
    ]),
    
    resources: useStorage('church-resources', [
      {
        id: 'resource-1',
        title: 'Bible Study Guide: Book of Romans',
        author: 'Pastor Johnson',
        type: 'study-guide',
        description: 'A comprehensive study guide for the Book of Romans.',
        fileUrl: '/media/resources/romans-study-guide.pdf',
        thumbnailUrl: '/media/thumbnails/romans-study-guide.jpg',
        tags: ['bible study', 'romans', 'paul'],
        downloads: 56,
        isPublished: true
      },
      {
        id: 'resource-2',
        title: 'Prayer Journal Template',
        author: 'Church Staff',
        type: 'template',
        description: 'A printable template for keeping a prayer journal.',
        fileUrl: '/media/resources/prayer-journal-template.pdf',
        thumbnailUrl: '/media/thumbnails/prayer-journal.jpg',
        tags: ['prayer', 'journal', 'template'],
        downloads: 78,
        isPublished: true
      }
    ]),
    
    series: useStorage('church-series', [
      {
        id: 'series-1',
        title: 'Peace That Passes Understanding',
        description: 'A series exploring God\'s peace in different aspects of life.',
        startDate: '2025-05-19',
        endDate: '2025-06-09',
        imageUrl: '/media/series/peace-series.jpg',
        sermonIds: ['sermon-1'],
        isActive: true
      },
      {
        id: 'series-2',
        title: 'Spiritual Disciplines',
        description: 'Exploring practices that help us grow in our faith.',
        startDate: '2025-04-14',
        endDate: '2025-05-12',
        imageUrl: '/media/series/spiritual-disciplines.jpg',
        sermonIds: ['sermon-2'],
        isActive: false
      }
    ]),
    
    speakers: useStorage('church-speakers', [
      {
        id: 'speaker-1',
        name: 'Pastor Johnson',
        role: 'Senior Pastor',
        bio: 'Pastor Johnson has been serving as the senior pastor for over 10 years.',
        imageUrl: '/media/speakers/pastor-johnson.jpg',
        sermonIds: ['sermon-1', 'sermon-2']
      },
      {
        id: 'speaker-2',
        name: 'Dr. Smith',
        role: 'Guest Speaker',
        bio: 'Dr. Smith is a renowned theologian and author of several books on Christian living.',
        imageUrl: '/media/speakers/dr-smith.jpg',
        sermonIds: ['sermon-3']
      }
    ]),
    
    settings: useStorage('media-settings', {
      defaultPlatforms: ['youtube', 'spotify', 'apple-podcasts'],
      autoPublish: false,
      notifySubscribers: true,
      defaultLicense: 'CC BY-NC-SA 4.0',
      enableComments: true,
      moderateComments: true,
      transcriptService: 'manual',
      storageQuota: {
        total: 50, // GB
        used: 12.4 // GB
      }
    }),
    
    stats: {
      totalSermons: 3,
      totalPodcasts: 1,
      totalDevotionals: 1,
      totalResources: 2,
      totalDownloads: 320,
      totalViews: 455,
      popularTags: ['faith', 'prayer', 'peace']
    }
  }),
  
  getters: {
    getSermonById: (state) => (id) => {
      return state.sermons.find(sermon => sermon.id === id);
    },
    
    getSermonsBySeries: (state) => (seriesId) => {
      const series = state.series.find(s => s.id === seriesId);
      if (!series) return [];
      
      return state.sermons.filter(sermon => series.sermonIds.includes(sermon.id));
    },
    
    getSermonsBySpeaker: (state) => (speakerId) => {
      const speaker = state.speakers.find(s => s.id === speakerId);
      if (!speaker) return [];
      
      return state.sermons.filter(sermon => speaker.sermonIds.includes(sermon.id));
    },
    
    getSermonsByTag: (state) => (tag) => {
      return state.sermons.filter(sermon => sermon.tags.includes(tag));
    },
    
    getRecentSermons: (state) => (count = 5) => {
      // Create a copy of the sermons array to avoid mutating the state
      const sortedSermons = [...state.sermons];
      
      // Sort by date (most recent first)
      sortedSermons.sort((a, b) => {
        return new Date(b.date) - new Date(a.date);
      });
      
      // Return the specified number of sermons
      return sortedSermons.slice(0, count);
    },
    
    getPodcastById: (state) => (id) => {
      return state.podcasts.find(podcast => podcast.id === id);
    },
    
    getPodcastEpisodeById: (state) => (podcastId, episodeId) => {
      const podcast = state.podcasts.find(p => p.id === podcastId);
      if (!podcast) return null;
      
      return podcast.episodes.find(episode => episode.id === episodeId);
    },
    
    getDevotionalById: (state) => (id) => {
      return state.devotionals.find(devotional => devotional.id === id);
    },
    
    getDevotionalEntryById: (state) => (devotionalId, entryId) => {
      const devotional = state.devotionals.find(d => d.id === devotionalId);
      if (!devotional) return null;
      
      return devotional.entries.find(entry => entry.id === entryId);
    },
    
    getResourceById: (state) => (id) => {
      return state.resources.find(resource => resource.id === id);
    },
    
    getResourcesByType: (state) => (type) => {
      return state.resources.filter(resource => resource.type === type);
    },
    
    getSeriesById: (state) => (id) => {
      return state.series.find(series => series.id === id);
    },
    
    getActiveSeries: (state) => {
      return state.series.filter(series => series.isActive);
    },
    
    getSpeakerById: (state) => (id) => {
      return state.speakers.find(speaker => speaker.id === id);
    },
    
    getPopularSermons: (state) => (count = 5) => {
      // Create a copy of the sermons array to avoid mutating the state
      const sortedSermons = [...state.sermons];
      
      // Sort by views (most viewed first)
      sortedSermons.sort((a, b) => {
        return b.views - a.views;
      });
      
      // Return the specified number of sermons
      return sortedSermons.slice(0, count);
    },
    
    getPopularResources: (state) => (count = 5) => {
      // Create a copy of the resources array to avoid mutating the state
      const sortedResources = [...state.resources];
      
      // Sort by downloads (most downloaded first)
      sortedResources.sort((a, b) => {
        return b.downloads - a.downloads;
      });
      
      // Return the specified number of resources
      return sortedResources.slice(0, count);
    }
  },
  
  actions: {
    // Sermons
    addSermon(sermon) {
      // Generate a unique ID if not provided
      if (!sermon.id) {
        sermon.id = 'sermon-' + Date.now();
      }
      
      // Set default values for optional fields
      sermon.downloads = sermon.downloads || 0;
      sermon.views = sermon.views || 0;
      sermon.tags = sermon.tags || [];
      sermon.isPublished = sermon.isPublished !== undefined ? sermon.isPublished : false;
      
      this.sermons.push(sermon);
      this.updateStats();
      
      // If the sermon belongs to a series, update the series
      if (sermon.series) {
        const seriesObj = this.series.find(s => s.title === sermon.series);
        if (seriesObj) {
          seriesObj.sermonIds.push(sermon.id);
        }
      }
      
      // If the sermon has a speaker, update the speaker
      if (sermon.speaker) {
        const speakerObj = this.speakers.find(s => s.name === sermon.speaker);
        if (speakerObj) {
          speakerObj.sermonIds.push(sermon.id);
        }
      }
      
      return sermon.id;
    },
    
    updateSermon(updatedSermon) {
      const index = this.sermons.findIndex(sermon => sermon.id === updatedSermon.id);
      
      if (index !== -1) {
        this.sermons[index] = { ...updatedSermon };
        this.updateStats();
        return true;
      }
      
      return false;
    },
    
    deleteSermon(id) {
      const index = this.sermons.findIndex(sermon => sermon.id === id);
      
      if (index !== -1) {
        const sermon = this.sermons[index];
        
        // Remove sermon from series
        if (sermon.series) {
          const seriesObj = this.series.find(s => s.title === sermon.series);
          if (seriesObj) {
            const sermonIndex = seriesObj.sermonIds.indexOf(id);
            if (sermonIndex !== -1) {
              seriesObj.sermonIds.splice(sermonIndex, 1);
            }
          }
        }
        
        // Remove sermon from speaker
        if (sermon.speaker) {
          const speakerObj = this.speakers.find(s => s.name === sermon.speaker);
          if (speakerObj) {
            const sermonIndex = speakerObj.sermonIds.indexOf(id);
            if (sermonIndex !== -1) {
              speakerObj.sermonIds.splice(sermonIndex, 1);
            }
          }
        }
        
        this.sermons.splice(index, 1);
        this.updateStats();
        return true;
      }
      
      return false;
    },
    
    incrementSermonViews(id) {
      const sermon = this.getSermonById(id);
      
      if (sermon) {
        sermon.views++;
        this.updateStats();
        return true;
      }
      
      return false;
    },
    
    incrementSermonDownloads(id) {
      const sermon = this.getSermonById(id);
      
      if (sermon) {
        sermon.downloads++;
        this.updateStats();
        return true;
      }
      
      return false;
    },
    
    // Podcasts
    addPodcast(podcast) {
      // Generate a unique ID if not provided
      if (!podcast.id) {
        podcast.id = 'podcast-' + Date.now();
      }
      
      // Set default values for optional fields
      podcast.episodes = podcast.episodes || [];
      podcast.subscribers = podcast.subscribers || 0;
      podcast.isPublished = podcast.isPublished !== undefined ? podcast.isPublished : false;
      
      this.podcasts.push(podcast);
      this.updateStats();
      return podcast.id;
    },
    
    addPodcastEpisode(podcastId, episode) {
      const podcast = this.getPodcastById(podcastId);
      
      if (podcast) {
        // Generate a unique ID if not provided
        if (!episode.id) {
          episode.id = 'episode-' + Date.now();
        }
        
        // Set default values for optional fields
        episode.downloads = episode.downloads || 0;
        
        podcast.episodes.push(episode);
        this.updateStats();
        return episode.id;
      }
      
      return false;
    },
    
    updatePodcast(updatedPodcast) {
      const index = this.podcasts.findIndex(podcast => podcast.id === updatedPodcast.id);
      
      if (index !== -1) {
        this.podcasts[index] = { ...updatedPodcast };
        this.updateStats();
        return true;
      }
      
      return false;
    },
    
    updatePodcastEpisode(podcastId, updatedEpisode) {
      const podcast = this.getPodcastById(podcastId);
      
      if (podcast) {
        const episodeIndex = podcast.episodes.findIndex(episode => episode.id === updatedEpisode.id);
        
        if (episodeIndex !== -1) {
          podcast.episodes[episodeIndex] = { ...updatedEpisode };
          this.updateStats();
          return true;
        }
      }
      
      return false;
    },
    
    deletePodcast(id) {
      const index = this.podcasts.findIndex(podcast => podcast.id === id);
      
      if (index !== -1) {
        this.podcasts.splice(index, 1);
        this.updateStats();
        return true;
      }
      
      return false;
    },
    
    deletePodcastEpisode(podcastId, episodeId) {
      const podcast = this.getPodcastById(podcastId);
      
      if (podcast) {
        const episodeIndex = podcast.episodes.findIndex(episode => episode.id === episodeId);
        
        if (episodeIndex !== -1) {
          podcast.episodes.splice(episodeIndex, 1);
          this.updateStats();
          return true;
        }
      }
      
      return false;
    },
    
    // Devotionals
    addDevotional(devotional) {
      // Generate a unique ID if not provided
      if (!devotional.id) {
        devotional.id = 'devotional-' + Date.now();
      }
      
      // Set default values for optional fields
      devotional.entries = devotional.entries || [];
      devotional.subscribers = devotional.subscribers || 0;
      devotional.isPublished = devotional.isPublished !== undefined ? devotional.isPublished : false;
      
      this.devotionals.push(devotional);
      this.updateStats();
      return devotional.id;
    },
    
    addDevotionalEntry(devotionalId, entry) {
      const devotional = this.getDevotionalById(devotionalId);
      
      if (devotional) {
        // Generate a unique ID if not provided
        if (!entry.id) {
          entry.id = 'entry-' + Date.now();
        }
        
        devotional.entries.push(entry);
        this.updateStats();
        return entry.id;
      }
      
      return false;
    },
    
    updateDevotional(updatedDevotional) {
      const index = this.devotionals.findIndex(devotional => devotional.id === updatedDevotional.id);
      
      if (index !== -1) {
        this.devotionals[index] = { ...updatedDevotional };
        this.updateStats();
        return true;
      }
      
      return false;
    },
    
    updateDevotionalEntry(devotionalId, updatedEntry) {
      const devotional = this.getDevotionalById(devotionalId);
      
      if (devotional) {
        const entryIndex = devotional.entries.findIndex(entry => entry.id === updatedEntry.id);
        
        if (entryIndex !== -1) {
          devotional.entries[entryIndex] = { ...updatedEntry };
          this.updateStats();
          return true;
        }
      }
      
      return false;
    },
    
    deleteDevotional(id) {
      const index = this.devotionals.findIndex(devotional => devotional.id === id);
      
      if (index !== -1) {
        this.devotionals.splice(index, 1);
        this.updateStats();
        return true;
      }
      
      return false;
    },
    
    deleteDevotionalEntry(devotionalId, entryId) {
      const devotional = this.getDevotionalById(devotionalId);
      
      if (devotional) {
        const entryIndex = devotional.entries.findIndex(entry => entry.id === entryId);
        
        if (entryIndex !== -1) {
          devotional.entries.splice(entryIndex, 1);
          this.updateStats();
          return true;
        }
      }
      
      return false;
    },
    
    // Resources
    addResource(resource) {
      // Generate a unique ID if not provided
      if (!resource.id) {
        resource.id = 'resource-' + Date.now();
      }
      
      // Set default values for optional fields
      resource.downloads = resource.downloads || 0;
      resource.tags = resource.tags || [];
      resource.isPublished = resource.isPublished !== undefined ? resource.isPublished : false;
      
      this.resources.push(resource);
      this.updateStats();
      return resource.id;
    },
    
    updateResource(updatedResource) {
      const index = this.resources.findIndex(resource => resource.id === updatedResource.id);
      
      if (index !== -1) {
        this.resources[index] = { ...updatedResource };
        this.updateStats();
        return true;
      }
      
      return false;
    },
    
    deleteResource(id) {
      const index = this.resources.findIndex(resource => resource.id === id);
      
      if (index !== -1) {
        this.resources.splice(index, 1);
        this.updateStats();
        return true;
      }
      
      return false;
    },
    
    incrementResourceDownloads(id) {
      const resource = this.getResourceById(id);
      
      if (resource) {
        resource.downloads++;
        this.updateStats();
        return true;
      }
      
      return false;
    },
    
    // Series
    addSeries(series) {
      // Generate a unique ID if not provided
      if (!series.id) {
        series.id = 'series-' + Date.now();
      }
      
      // Set default values for optional fields
      series.sermonIds = series.sermonIds || [];
      series.isActive = series.isActive !== undefined ? series.isActive : false;
      
      this.series.push(series);
      this.updateStats();
      return series.id;
    },
    
    updateSeries(updatedSeries) {
      const index = this.series.findIndex(series => series.id === updatedSeries.id);
      
      if (index !== -1) {
        this.series[index] = { ...updatedSeries };
        this.updateStats();
        return true;
      }
      
      return false;
    },
    
    deleteSeries(id) {
      const index = this.series.findIndex(series => series.id === id);
      
      if (index !== -1) {
        this.series.splice(index, 1);
        this.updateStats();
        return true;
      }
      
      return false;
    },
    
    // Speakers
    addSpeaker(speaker) {
      // Generate a unique ID if not provided
      if (!speaker.id) {
        speaker.id = 'speaker-' + Date.now();
      }
      
      // Set default values for optional fields
      speaker.sermonIds = speaker.sermonIds || [];
      
      this.speakers.push(speaker);
      this.updateStats();
      return speaker.id;
    },
    
    updateSpeaker(updatedSpeaker) {
      const index = this.speakers.findIndex(speaker => speaker.id === updatedSpeaker.id);
      
      if (index !== -1) {
        this.speakers[index] = { ...updatedSpeaker };
        this.updateStats();
        return true;
      }
      
      return false;
    },
    
    deleteSpeaker(id) {
      const index = this.speakers.findIndex(speaker => speaker.id === id);
      
      if (index !== -1) {
        this.speakers.splice(index, 1);
        this.updateStats();
        return true;
      }
      
      return false;
    },
    
    // Settings
    updateSettings(newSettings) {
      this.settings = { ...this.settings, ...newSettings };
      return true;
    },
    
    // Stats
    updateStats() {
      // Calculate total counts
      this.stats.totalSermons = this.sermons.length;
      this.stats.totalPodcasts = this.podcasts.length;
      this.stats.totalDevotionals = this.devotionals.length;
      this.stats.totalResources = this.resources.length;
      
      // Calculate total downloads
      this.stats.totalDownloads = 
        this.sermons.reduce((sum, sermon) => sum + sermon.downloads, 0) +
        this.resources.reduce((sum, resource) => sum + resource.downloads, 0) +
        this.podcasts.reduce((sum, podcast) => 
          sum + podcast.episodes.reduce((epSum, episode) => epSum + episode.downloads, 0), 0);
      
      // Calculate total views
      this.stats.totalViews = this.sermons.reduce((sum, sermon) => sum + sermon.views, 0);
      
      // Calculate popular tags
      const tagCounts = {};
      
      // Count tags from sermons
      this.sermons.forEach(sermon => {
        sermon.tags.forEach(tag => {
          tagCounts[tag] = (tagCounts[tag] || 0) + 1;
        });
      });
      
      // Count tags from resources
      this.resources.forEach(resource => {
        resource.tags.forEach(tag => {
          tagCounts[tag] = (tagCounts[tag] || 0) + 1;
        });
      });
      
      // Sort tags by count and get the top 5
      this.stats.popularTags = Object.entries(tagCounts)
        .sort((a, b) => b[1] - a[1])
        .slice(0, 5)
        .map(entry => entry[0]);
    }
  }
});
