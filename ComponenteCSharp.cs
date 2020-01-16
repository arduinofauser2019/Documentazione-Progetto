/*
 *          ITT G. Fauser
 *     OpenDay - 18/01/2020
 *     
 *     Progetto in Arduino: Misurazioni ambientali
 *                          Componente C#
 */

using System;
using System.IO;
using System.IO.Ports;
using System.Net;
using System.Threading;
using System.Threading.Tasks;

namespace TemperaturaUmiditaFTP_HTML
{
    class Program
    {
        // Semaforo = gestione della mutua esclusione
        static SemaphoreSlim mutex = new SemaphoreSlim(1);
        static void Main(string[] args)
        {
            // Stringa costante, indicante il laboratorio di posizionamento di Arduino
            const string LABORATORIO = "LS";
            // Valore booleano che indica la volontà di voler eliminare le misurazioni effettuate per il laboratorio corrente
            const bool CANCELLAPRECEDENTI = false;
            // Classe SerialPort, specializzata nella gestione delle stringhe in input dalle porte seriali
            SerialPort portaSeriale = new SerialPort();
            // Baud Rate pari a 9600
            portaSeriale.BaudRate = 9600;
            // Nome corrispondente alla porta scelta
            portaSeriale.PortName = "COM7";
            portaSeriale.Open();
            // Delay tra le letture delle misurazioni (500 ms). Deve essere corrispondente a quello indicato nello script di Arduino
            // Per evitare problemi di bufferizzazione
            const int RITARDO = 500;
            // Valori di luminosità e umidità che indicano il range di valori entro i quali possono spaziare le misurazioni prima di essere scritte sul server
            const int DIFFERENZAUMIDITA = 5;
            const int DIFFERENZALUMINOSITA = 10;
            // Variabili di memorizzazione delle ultime misurazioni scritte
            int umiditaPrec = -1, luminositaPrec = -1;
            double temperaturaPrec = -1, indiceCalorePrec = -1;
            // Se si è scelto di cancellare le misurazioni precedenti...
            if (CANCELLAPRECEDENTI)
            {
                // ... si crea un file da zero
                using (FileStream FS = new FileStream("misure" + LABORATORIO + ".txt", FileMode.Create))
                    FS.Close();
            }
            while (true)
            {
                try
                {
                    Console.WriteLine("Valori letti in tempo reale: ");
                    Console.WriteLine();
                    /* 
                     * Esempio di riga letta: 
                     * Umidita': 62.00 % Temperatura: 25.30 ??C Indice di calore: 25.50 ??C Luminosita': 59 %
                     */
                    // Lettura dei valori e parsing dei valori letti in funzione della posizione nella stringa.
                    string lettura = portaSeriale.ReadLine().Replace("??", " °");
                    int umidita = Convert.ToInt32(Convert.ToDouble(lettura.Split(' ')[1].Substring(0, lettura.Split(' ')[1].Length - 2).Replace(".", ",")));
                    double temperatura = Convert.ToDouble(lettura.Split(' ')[4].Replace(".", ","));
                    double indiceCalore = Convert.ToDouble(lettura.Split(' ')[10].Replace(".", ","));
                    int luminosita = Convert.ToInt32(lettura.Split(' ')[14].Replace(".", ","));
                    // Stampa delle misurazioni in tabella.
                    Console.WriteLine("==========================================================");
                    Console.WriteLine(" UMIDITA'    TEMPERATURA    INDICE DI CALORE   LUMINOSITA'");
                    Console.WriteLine("==========================================================");
                    Console.WriteLine("{0,5} {1,15} {2,16} {3,14}", umidita, temperatura.ToString("#.00"), indiceCalore.ToString("#.00"), luminosita);
                    Console.WriteLine("==========================================================");
                    // Accedo ad una risorsa condivisa. Lo faccio utilizzando la mutua esclusione
                    mutex.Wait();
                    // Utilizzo delle classi FileStream e StreamWriter per la memorizzazione delle misurazioni su file di testo
                    using (FileStream FS = new FileStream("misure" + LABORATORIO + ".txt", FileMode.Append))
                    using (StreamWriter SW = new StreamWriter(FS))
                    {
                        // Se vi è una differenza (in valore assoluto), pari ad almeno il tetto prefissato...
                        if (Math.Abs(umidita - umiditaPrec) >= DIFFERENZAUMIDITA || Math.Abs(luminosita - luminositaPrec) >= DIFFERENZALUMINOSITA)
                        {
                            // ... si procede con la scrittura su file
                            SW.Write(String.Format("{0};{1};{2};" + luminosita, umidita, temperatura.ToString("#.00"), indiceCalore.ToString("#.00")).Replace(",", "."));
                            SW.WriteLine();
                            SW.Close();
                            umiditaPrec = umidita;
                            temperaturaPrec = temperatura;
                            indiceCalorePrec = indiceCalore;
                            luminositaPrec = luminosita;
                            Console.WriteLine();
                            Console.WriteLine("Rilevata nuova misura valida. Avvio aggiornamento contatori su server...");
                            // Delego ad un nuovo thread generato all'istante il compito di gestire la connessione TCP/FTP, per non creare problemi di tempistiche di lettura
                            Task.Factory.StartNew(() =>
                            {
                                // Accedo al file solo se non è in uso da parte di altri thread, come il Main Thread
                                mutex.Wait();
                                scriviFTP1(LABORATORIO);
                                mutex.Release();
                            });
                        }
                        else
                        {
                            Console.WriteLine();
                            Console.WriteLine("Valori poco dissimili dai precedenti. Inizio nuova rilettura...");
                        }
                    }
                    // Rilascio il semaforo: la risorsa condivisa (file) non è più in uso
                    mutex.Release();
                    Thread.Sleep(RITARDO);
                    Console.Clear();
                }
                catch (Exception)
                {
                    Console.WriteLine("Valore invalido. Scartato.");
                }
            }
        }
        // Routine di scrittura su Server FTP
        static void scriviFTP1(string LAB)
        {
            try
            {
                // Creazione di un'istanza di classe WebClient (che sa gestire le connessioni TCP con protocollo FTP)
                using (var client = new WebClient())
                {
                    // Credenziali di accesso al server
                    client.Credentials = new NetworkCredential("nome-utente", "password");
                    // Si suppone la root directory di FTP sia anche la cartella di upload del file
                    client.UploadFile("ftp://URLSERVER:PORTA/misure" + LAB + ".txt", WebRequestMethods.Ftp.UploadFile, "misure" + LAB + ".txt");
                }
            }
            catch (Exception e)
            {
                Console.WriteLine("Errore FTP: " + e);
            }
        }
    }
}
