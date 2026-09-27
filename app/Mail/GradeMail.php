<?php

namespace App\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;

class GradeMail extends Mailable
{
    use Queueable, SerializesModels;

    public $toName;
    public $courseName;
    public $grade;
    public $gradeDetails;

    /**
     * Create a new message instance.
     */
    public function __construct($toName, $courseName, $grade, $gradeDetails = [])
    {
        $this->toName = $toName;
        $this->courseName = $courseName;
        $this->grade = $grade;
        $this->gradeDetails = $gradeDetails;
    }

    /**
     * Build the message.
     */
    public function build()
    {
        $colorNota = ($this->grade >= 70) ? '#198754' : '#dc3545';
        $mensajeNota = ($this->grade >= 70) ? '¡Felicidades! Ha aprobado el curso.' : 'El curso no ha sido aprobado.';

        $detailsTable = '';
        if (!empty($this->gradeDetails)) {
            $detailsTable .= '<h4 style="margin-top: 30px; border-bottom: 1px solid #ccc; padding-bottom: 5px;">Desglose de Calificación</h4>';
            $detailsTable .= '<table style="width: 100%; border-collapse: collapse; margin-top: 15px;">';
            $detailsTable .= '<thead><tr style="background-color: #f2f2f2;">
                                <th style="padding: 8px; border: 1px solid #ddd; text-align: left;">Rubro de Evaluación</th>
                                <th style="padding: 8px; border: 1px solid #ddd; text-align: center;">Porcentaje</th>
                                <th style="padding: 8px; border: 1px solid #ddd; text-align: center;">Calificación</th>
                              </tr></thead>';
            $detailsTable .= '<tbody>';
            foreach ($this->gradeDetails as $detail) {
                $detailsTable .= '<tr>
                                    <td style="padding: 8px; border: 1px solid #ddd;">' . htmlspecialchars($detail['rubro']) . '</td>
                                    <td style="padding: 8px; border: 1px solid #ddd; text-align: center;">' . htmlspecialchars($detail['porcentaje']) . '%</td>
                                    <td style="padding: 8px; border: 1px solid #ddd; text-align: center;">' . htmlspecialchars($detail['calificacion']) . '</td>
                                  </tr>';
            }
            $detailsTable .= '</tbody></table>';
        }

        $body = '
            <html>
            <head>
                <style>
                    body { font-family: "Helvetica Neue", Helvetica, Arial, sans-serif; background-color: #f4f4f4; margin: 0; padding: 0; }
                    .email-container { max-width: 600px; margin: 20px auto; background-color: #ffffff; border-radius: 8px; overflow: hidden; box-shadow: 0 4px 6px rgba(0,0,0,0.1); }
                    .header { background-color: #003366; color: #ffffff; padding: 20px; text-align: center; }
                    .content { padding: 30px; color: #333333; line-height: 1.6; }
                    .grade-box { text-align: center; margin: 25px 0; padding: 20px; background-color: #f8f9fa; border-radius: 8px; border-left: 5px solid ' . $colorNota . '; }
                    .grade-value { font-size: 48px; font-weight: bold; color: ' . $colorNota . '; margin: 0; }
                    .footer { background-color: #eeeeee; padding: 15px; text-align: center; font-size: 12px; color: #777777; }
                </style>
            </head>
            <body>
                <div class="email-container">
                    <div class="header">
                        <h1 style="margin:0;">Reporte de Calificaciones</h1>
                    </div>
                    <div class="content">
                        <p>Estimado(a) <strong>' . htmlspecialchars($this->toName) . '</strong>,</p>
                        <p>Se ha registrado su calificación final para el curso: <strong>' . htmlspecialchars($this->courseName) . '</strong>.</p>
                        
                        <div class="grade-box">
                            <p style="margin:0; font-size:14px; text-transform:uppercase; color:#777;">Calificación Final</p>
                            <p class="grade-value">' . number_format($this->grade, 2) . '</p>
                            <p style="margin-top:10px; font-weight:bold;">' . $mensajeNota . '</p>
                        </div>

                        ' . $detailsTable . '

                        <p>Si tiene alguna duda, por favor comuníquese con su profesor o con el departamento de registro.</p>
                    </div>
                    <div class="footer">
                        <p>&copy; ' . date('Y') . ' ' . config('cliente.nombre_legal', config('cliente.nombre', 'CEFI')) . '. Todos los derechos reservados.</p>
                    </div>
                </div>
            </body>
            </html>
        ';

        return $this->subject('Calificación Final: ' . $this->courseName)
                    ->html($body);
    }
}
